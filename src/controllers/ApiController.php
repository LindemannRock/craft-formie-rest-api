<?php
/**
 * Formie API Controller
 *
 * Provides REST API endpoints for accessing Formie forms and submissions
 *
 * @author LindemannRock
 * @copyright Copyright (c) 2025 LindemannRock
 * @link https://lindemannrock.com
 * @package FormieRestApi
 * @since 1.0.0
 */

namespace lindemannrock\formierestapi\controllers;

use Craft;
use craft\db\Query;
use craft\db\Table as CraftTable;
use craft\web\Controller;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\formierestapi\FormieRestApi;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\helpers\Table as FormieTable;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;
use yii\web\UnauthorizedHttpException;

class ApiController extends Controller
{
    /**
     * Allow anonymous access with API key authentication
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * @var array<string, mixed>|null Resolved API key data for the current request.
     */
    private ?array $apiKeyData = null;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // Validate API key for all actions
        $apiKey = Craft::$app->request->getHeaders()->get('X-API-Key');
        $apiKeyData = FormieRestApi::$plugin->apiKey->validateApiKey($apiKey);

        if (!$apiKeyData) {
            throw new UnauthorizedHttpException('Invalid or missing API key');
        }

        // Enforce HMAC signing if this key has a signing secret configured
        // (FORMIE_API_SIGNING_SECRET[_LIMITED|_TEST]). Opt-in per key.
        if (!empty($apiKeyData['requireSignature'])
            && !FormieRestApi::$plugin->security->validateRequestSignature($apiKeyData)
        ) {
            throw new UnauthorizedHttpException('Missing or invalid request signature');
        }

        // Enforce IP whitelist if this key has one configured
        // (FORMIE_API_IP_WHITELIST[_LIMITED|_TEST]). Opt-in per key.
        if (!FormieRestApi::$plugin->security->validateIpWhitelist($apiKeyData)) {
            throw new UnauthorizedHttpException('Request originates from an IP not allowed for this key');
        }

        $this->apiKeyData = $apiKeyData;

        // Set response format to JSON
        Craft::$app->response->format = Response::FORMAT_JSON;

        // Rate limiting (counter persisted in Craft cache, fixed 1-hour window)
        $allowed = FormieRestApi::$plugin->security->checkRateLimit((string) $apiKey, $apiKeyData);

        // Always advertise the budget on success and 429 alike
        foreach (FormieRestApi::$plugin->security->getRateLimitHeaders((string) $apiKey, $apiKeyData) as $name => $value) {
            Craft::$app->response->headers->set($name, $value);
        }

        if (!$allowed) {
            throw new TooManyRequestsHttpException(message: 'API rate limit exceeded. Try again later.');
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $apiKey = Craft::$app->request->getHeaders()->get('X-API-Key');
        if (is_string($apiKey) && $apiKey !== '') {
            FormieRestApi::$plugin->security->logApiAccess(
                $apiKey,
                Craft::$app->request->getUrl(),
                Craft::$app->request->getQueryParams(),
                Craft::$app->response->statusCode,
            );
        }

        return parent::afterAction($action, $result);
    }

    /**
     * Throw 403 unless the resolved key has the given permission scope.
     */
    private function requireApiPermission(string $permission): void
    {
        if (!FormieRestApi::$plugin->apiKey->hasPermission($this->apiKeyData ?? [], $permission)) {
            throw new ForbiddenHttpException("API key does not have permission: {$permission}");
        }
    }

    /**
     * Validate a `dateFrom` / `dateTo` query param and return a `Y-m-d H:i:s`
     * string ready for use in an element-query date filter, or null if the
     * param was absent. Throws 400 on unparseable input.
     *
     * Date-only inputs (`YYYY-MM-DD`) are pinned to end-of-day when `$endOfDay`
     * is true, so a `dateTo=2026-04-28` filter is inclusive of the whole day.
     */
    private function parseDateFilter(?string $value, string $paramName, bool $endOfDay): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $dt = DateFormatHelper::toCraftTimezone($value);
        if ($dt === null) {
            throw new BadRequestHttpException(
                "Invalid {$paramName} value — expected YYYY-MM-DD or YYYY-MM-DD HH:MM:SS or ISO 8601"
            );
        }
        if ($endOfDay && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            $dt->setTime(23, 59, 59);
        }
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Get all forms
     * GET /api/v1/formie/forms
     */
    public function actionForms(): array
    {
        $this->requireApiPermission('read_forms');

        $request = Craft::$app->request;
        
        // Get query parameters
        $limit = (int) $request->getParam('limit', 100);
        $offset = (int) $request->getParam('offset', 0);
        $status = $request->getParam('status', 'enabled');
        
        // Build query
        $query = Form::find();

        if ($status !== 'all') {
            $query->status($status);
        }

        // Run COUNT against a clone before applying limit/offset — avoids
        // re-preparing the (already-executed) main query for the count.
        $total = (clone $query)->count();

        $query->limit($limit)->offset($offset);

        // Get forms
        /** @var \verbb\formie\elements\Form[] $forms */
        $forms = $query->all();

        // Batch-fetch submission counts for all forms in one query (avoids N+1).
        // Joins elements to honour ElementQuery's default `dateDeleted IS NULL`,
        // and excludes drafts + spam to match the /submissions endpoint contract.
        $countMap = [];
        $formIds = array_map(static fn(Form $f) => $f->id, $forms);
        if ($formIds) {
            $rows = (new Query())
                ->from(['s' => FormieTable::FORMIE_SUBMISSIONS])
                ->innerJoin(['e' => CraftTable::ELEMENTS], '[[s.id]] = [[e.id]]')
                ->where([
                    'e.dateDeleted' => null,
                    's.isIncomplete' => false,
                    's.isSpam' => false,
                    's.formId' => $formIds,
                ])
                ->groupBy(['s.formId'])
                ->select(['s.formId', 'cnt' => new Expression('COUNT(*)')])
                ->all();
            $countMap = array_column($rows, 'cnt', 'formId');
        }

        // Format response
        $formData = [];
        foreach ($forms as $form) {
            $formData[] = $this->transformForm($form, false, (int) ($countMap[$form->id] ?? 0));
        }
        
        return [
            'success' => true,
            'data' => $formData,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'timestamp' => (new \DateTime())->format('c'),
            ],
        ];
    }

    /**
     * Get form detail by ID
     * GET /api/v1/formie/forms/{id}
     */
    public function actionFormDetail(int $formId): array
    {
        $this->requireApiPermission('read_forms');

        /** @var \verbb\formie\elements\Form|null $form */
        $form = Form::find()->id($formId)->one();

        if (!$form) {
            throw new NotFoundHttpException("Form with ID {$formId} not found");
        }
        
        return [
            'success' => true,
            'data' => $this->transformForm($form, true),
            'meta' => [
                'timestamp' => (new \DateTime())->format('c'),
            ],
        ];
    }

    /**
     * Get form detail by handle
     * GET /api/v1/formie/forms/{handle}
     */
    public function actionFormByHandle(string $handle): array
    {
        $this->requireApiPermission('read_forms');

        /** @var \verbb\formie\elements\Form|null $form */
        $form = Form::find()->handle($handle)->one();

        if (!$form) {
            throw new NotFoundHttpException("Form with handle '{$handle}' not found");
        }

        return [
            'success' => true,
            'data' => $this->transformForm($form, true),
            'meta' => [
                'timestamp' => (new \DateTime())->format('c'),
            ],
        ];
    }

    /**
     * Get submissions
     * GET /api/v1/formie/submissions
     */
    public function actionSubmissions(): array
    {
        $this->requireApiPermission('read_submissions');

        $request = Craft::$app->request;
        
        // Get query parameters
        $formId = $request->getParam('formId');
        $formHandle = $request->getParam('formHandle');
        $status = $request->getParam('status', 'live');
        $limit = (int) $request->getParam('limit', 100);
        $offset = (int) $request->getParam('offset', 0);
        $dateFrom = $request->getParam('dateFrom');
        $dateTo = $request->getParam('dateTo');
        
        // Build query — exclude abandoned drafts and Akismet-flagged spam
        // (matches the test-endpoint behaviour and the documented API contract)
        $query = Submission::find()
            ->isIncomplete(false)
            ->isSpam(false);

        // Filter by form
        if ($formHandle) {
            $form = Form::find()->handle($formHandle)->one();
            if (!$form) {
                throw new BadRequestHttpException("Form with handle '{$formHandle}' not found");
            }
            $query->formId($form->id);
        } elseif ($formId) {
            $query->formId($formId);
        }
        
        // Filter by status
        if ($status !== 'all') {
            $query->status($status);
        }
        
        // Date filters — validate before passing to the query builder
        $dateFromStr = $this->parseDateFilter($dateFrom, 'dateFrom', false);
        $dateToStr = $this->parseDateFilter($dateTo, 'dateTo', true);
        if ($dateFromStr !== null) {
            $query->dateCreated('>= ' . $dateFromStr);
        }
        if ($dateToStr !== null) {
            $query->dateCreated('<= ' . $dateToStr);
        }
        
        // Run COUNT against a clone before applying limit/offset/order — avoids
        // re-preparing the (already-executed) main query for the count.
        $total = (clone $query)->count();

        // Apply limit and offset
        $query->limit($limit)->offset($offset);

        // Order by newest first
        $query->orderBy('dateCreated DESC');

        // Get submissions
        /** @var \verbb\formie\elements\Submission[] $submissions */
        $submissions = $query->all();

        // Format response
        $submissionData = [];
        foreach ($submissions as $submission) {
            $submissionData[] = $this->transformSubmission($submission);
        }
        
        return [
            'success' => true,
            'data' => $submissionData,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'timestamp' => (new \DateTime())->format('c'),
            ],
        ];
    }

    /**
     * Get submission detail
     * GET /api/v1/formie/submissions/{id}
     */
    public function actionSubmissionDetail(int $submissionId): array
    {
        $this->requireApiPermission('read_submissions');

        /** @var \verbb\formie\elements\Submission|null $submission */
        $submission = Submission::find()->id($submissionId)->one();

        if (!$submission) {
            throw new NotFoundHttpException("Submission with ID {$submissionId} not found");
        }
        
        return [
            'success' => true,
            'data' => $this->transformSubmission($submission, true),
            'meta' => [
                'timestamp' => (new \DateTime())->format('c'),
            ],
        ];
    }

    /**
     * Transform form for API response.
     *
     * Pass `$submissionCount` when batching across many forms to avoid N+1.
     * When null (single-form detail endpoints), the count is queried inline.
     */
    private function transformForm(Form $form, bool $includeFields = false, ?int $submissionCount = null): array
    {
        $data = [
            'id' => $form->id,
            'uid' => $form->uid,
            'handle' => $form->handle,
            'title' => $form->title,
            'status' => $form->status,
            'dateCreated' => $form->dateCreated->format('c'),
            'dateUpdated' => $form->dateUpdated->format('c'),
            'submissionCount' => $submissionCount ?? Submission::find()
                ->formId($form->id)
                ->isIncomplete(false)
                ->isSpam(false)
                ->count(),
        ];
        
        if ($includeFields) {
            $data['fields'] = FormieRestApi::$plugin->transformer->getFormFields($form);
            $data['pages'] = $this->getFormPages($form);
        }
        
        return $data;
    }

    /**
     * Transform submission for API response
     */
    private function transformSubmission(Submission $submission, bool $includeForm = false): array
    {
        $form = $submission->getForm();

        $data = [
            'id' => $submission->id,
            'uid' => $submission->uid,
            'formId' => $form?->id,
            'formHandle' => $form?->handle,
            'status' => $submission->status,
            'dateCreated' => $submission->dateCreated->format('c'),
            'dateUpdated' => $submission->dateUpdated->format('c'),
            'fields' => FormieRestApi::$plugin->transformer->transformSubmissionFields($submission),
        ];

        if ($includeForm && $form !== null) {
            $data['form'] = $this->transformForm($form);
        }

        return $data;
    }

    /**
     * Get form pages
     */
    private function getFormPages(Form $form): array
    {
        $pages = [];
        
        foreach ($form->getPages() as $page) {
            $pageData = [
                'id' => $page->id,
                'label' => $page->label,
                'sortOrder' => $page->sortOrder,
                'fields' => [],
            ];
            
            foreach ($page->getCustomFields() as $field) {
                $pageData['fields'][] = [
                    'handle' => $field->handle,
                    'label' => $field->label,
                    'type' => basename(str_replace('\\', '/', get_class($field))),
                ];
            }
            
            $pages[] = $pageData;
        }
        
        return $pages;
    }
}
