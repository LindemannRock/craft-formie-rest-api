<?php
/**
 * API Test Controller for Formie REST API Module
 *
 * This controller provides sample endpoints to demonstrate
 * what data an external API consumer would receive from API queries
 *
 * @author LindemannRock
 * @copyright Copyright (c) 2025-2026 LindemannRock
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
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\traits\ApiKeyScopeTrait;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\helpers\Table as FormieTable;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;
use yii\web\UnauthorizedHttpException;

class ApiTestController extends Controller
{
    use ApiKeyScopeTrait;

    /**
     * Allow anonymous access — auth is enforced via X-API-Key in beforeAction().
     * Routes are only registered when devMode is on (see FormieRestApi::init()).
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * @var string|null The raw X-API-Key header for the current request, cached
     * in beforeAction() so afterAction() reuses it without re-reading the header.
     */
    private ?string $apiKey = null;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        $apiKey = Craft::$app->request->getHeaders()->get('X-API-Key');
        $apiKeyData = FormieRestApi::$plugin->apiKey->validateApiKey($apiKey);

        if (!$apiKeyData) {
            throw new UnauthorizedHttpException('Invalid or missing API key');
        }

        // Enforce HMAC signing if this key requires it (per-key toggle).
        if (!empty($apiKeyData['requireSignature'])
            && !FormieRestApi::$plugin->security->validateRequestSignature($apiKeyData)
        ) {
            throw new UnauthorizedHttpException('Missing or invalid request signature');
        }

        // Enforce IP whitelist if this key has one configured (per-key).
        if (!FormieRestApi::$plugin->security->validateIpWhitelist($apiKeyData)) {
            throw new UnauthorizedHttpException('Request originates from an IP not allowed for this key');
        }

        $this->apiKeyData = $apiKeyData;
        $this->apiKey = $apiKey;

        // Track the key's last-used timestamp, mirroring the production
        // controller so test-endpoint hits record usage too. The instanceof
        // narrows the mixed array value to ApiKey for static analysis.
        if (($apiKeyData['dbKey'] ?? null) instanceof ApiKey) {
            FormieRestApi::$plugin->apiKey->recordUsage($apiKeyData['dbKey']);
        }

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
        $apiKey = $this->apiKey;
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
     * Validate a `dateFrom` / `dateTo` query param and return a `Y-m-d H:i:s`
     * string ready for use in an element-query date filter, or null if the
     * param was absent. Throws 400 on unparseable input.
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
     * Test endpoint to show available forms
     * URL: /api/test/formie/forms
     * URL: /api/test/formie/forms?handle=customerFeedback
     * URL: /api/test/formie/forms?id=123
     * Header: X-API-Key: your-api-key-here
     */
    public function actionForms(): Response
    {
        $this->requireApiPermission('read_forms');

        // Read filter params + resolve form scope before the try so a 403 from
        // an out-of-scope handle surfaces as a real ForbiddenHttpException,
        // not swallowed into a 200 error payload by the catch below.
        $formHandle = Craft::$app->request->getParam('handle');
        $formId = Craft::$app->request->getParam('id');
        $scoped = $this->scopedFormHandles();

        if ($formHandle) {
            $this->requireFormInScope((string) $formHandle);
        }

        try {
            
            // Build query
            $query = Form::find()->status('enabled');
            
            // Apply filters
            if ($formHandle) {
                $query->handle($formHandle);
            } elseif ($formId) {
                $query->id($formId);
            } elseif ($scoped !== null) {
                // No filter requested: constrain a scoped key to its allowed forms.
                $query->handle($scoped);
            }
            
            // Get forms
            /** @var \verbb\formie\elements\Form[] $forms */
            $forms = $query->all();

            // A scoped key requesting a specific id must not see out-of-scope forms.
            if ($formId && $scoped !== null) {
                $forms = array_values(array_filter(
                    $forms,
                    static fn(Form $f): bool => in_array($f->handle, $scoped, true),
                ));
            }

            // If filtering by handle/id and no form found
            if (($formHandle || $formId) && empty($forms)) {
                return $this->asJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => $formHandle
                            ? "Form with handle '{$formHandle}' not found"
                            : "Form with ID '{$formId}' not found",
                    ],
                ]);
            }
            
            // Batch-fetch submission counts to avoid N+1 (one grouped query, joined
            // with elements to honour ElementQuery's default `dateDeleted IS NULL`,
            // and excluding drafts + spam to match the /submissions endpoint contract).
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

            $formData = [];
            foreach ($forms as $form) {
                $formData[] = [
                    'id' => $form->id,
                    'uid' => $form->uid,
                    'handle' => $form->handle,
                    'title' => $form->title,
                    'dateCreated' => $form->dateCreated->format('c'),
                    'dateUpdated' => $form->dateUpdated->format('c'),
                    'submissionCount' => (int) ($countMap[$form->id] ?? 0),
                    'fields' => FormieRestApi::$plugin->transformer->getFormFields($form),
                ];
            }
            
            return $this->asJson([
                'success' => true,
                'data' => [
                    'forms' => $formData,
                    'totalForms' => count($formData),
                ],
                'meta' => [
                    'timestamp' => (new \DateTime())->format('c'),
                    'endpoint' => 'forms',
                ],
            ]);
        } catch (\Throwable $e) {
            Craft::error('API Test Error: ' . $e->getMessage(), __METHOD__);

            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'FORMS_FETCH_ERROR',
                    'message' => 'Failed to fetch forms',
                    'detail' => Craft::$app->config->general->devMode ? $e->getMessage() : null,
                ],
            ]);
        }
    }
    
    /**
     * Test endpoint to show submissions for a specific form
     * URL: /api/test/formie/submissions?formHandle=customerFeedback
     * Header: X-API-Key: your-api-key-here
     */
    public function actionSubmissions(): Response
    {
        $this->requireApiPermission('read_submissions');

        $request = Craft::$app->request;
        $formHandle = $request->getParam('formHandle');
        $formId = $request->getParam('formId');
        $limit = max(1, (int) $request->getParam('limit', 10));
        $page = max(1, (int) $request->getParam('page', 1));
        $status = $request->getParam('status');

        // Validate date params before entering the try block — `BadRequestHttpException`
        // here must surface as a real 400, not be swallowed by the catch below.
        $dateFromStr = $this->parseDateFilter($request->getParam('dateFrom'), 'dateFrom', false);
        $dateToStr = $this->parseDateFilter($request->getParam('dateTo'), 'dateTo', true);

        if (!$formHandle && !$formId) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_PARAMETER',
                    'message' => 'Either formHandle or formId parameter is required',
                ],
            ]);
        }

        // Form-scope check before the try so an out-of-scope 403 isn't swallowed
        // into a 200 error payload by the catch below. A handle is checked
        // directly; an id is resolved to its form first.
        if ($formHandle) {
            $this->requireFormInScope((string) $formHandle);
        } elseif ($this->scopedFormHandles() !== null) {
            /** @var \verbb\formie\elements\Form|null $scopedForm */
            $scopedForm = Form::find()->id($formId)->status('enabled')->one();
            if ($scopedForm instanceof Form) {
                $this->requireFormInScope($scopedForm->handle);
            }
            // Unknown id falls through; the lookup below returns FORM_NOT_FOUND.
        }

        try {
            // Get the form
            $formQuery = Form::find()->status('enabled');

            if ($formHandle) {
                $formQuery->handle($formHandle);
            } else {
                $formQuery->id($formId);
            }

            /** @var \verbb\formie\elements\Form|null $form */
            $form = $formQuery->one();

            if (!$form) {
                return $this->asJson([
                    'success' => false,
                    'error' => [
                        'code' => 'FORM_NOT_FOUND',
                        'message' => $formHandle
                            ? "Form with handle '{$formHandle}' not found"
                            : "Form with ID '{$formId}' not found",
                    ],
                ]);
            }

            // Build query without limit/offset so count reflects the full result set
            $query = Submission::find()
                ->formId($form->id)
                ->isIncomplete(false)
                ->isSpam(false)
                ->orderBy('dateCreated DESC');

            // Craft ElementQuery's dateCreated() is a setter, not a chainer —
            // combine into one call when both are set so neither is lost.
            if ($dateFromStr !== null && $dateToStr !== null) {
                $query->dateCreated(['and', '>= ' . $dateFromStr, '<= ' . $dateToStr]);
            } elseif ($dateFromStr !== null) {
                $query->dateCreated('>= ' . $dateFromStr);
            } elseif ($dateToStr !== null) {
                $query->dateCreated('<= ' . $dateToStr);
            }
            if ($status) {
                $query->status($status);
            }

            // Count against a clone before applying limit/offset (mirrors 2.3/2.4 fix)
            $total = (int) (clone $query)->count();
            $offset = ($page - 1) * $limit;

            /** @var \verbb\formie\elements\Submission[] $submissions */
            $submissions = $query
                ->limit($limit)
                ->offset($offset)
                ->all();

            $submissionData = [];
            foreach ($submissions as $submission) {
                $submissionData[] = [
                    'id' => $submission->id,
                    'uid' => $submission->uid,
                    'title' => $submission->title,
                    'dateCreated' => $submission->dateCreated->format('c'),
                    'dateUpdated' => $submission->dateUpdated->format('c'),
                    'status' => $submission->getStatus(),
                    'fields' => FormieRestApi::$plugin->transformer->transformSubmissionFields($submission),
                ];
            }
            
            return $this->asJson([
                'success' => true,
                'data' => [
                    'form' => [
                        'id' => $form->id,
                        'handle' => $form->handle,
                        'title' => $form->title,
                    ],
                    'submissions' => $submissionData,
                    'pagination' => [
                        'total' => $total,
                        'perPage' => $limit,
                        'currentPage' => $page,
                        'totalPages' => (int) ceil($total / $limit),
                        'hasMore' => ($offset + $limit) < $total,
                    ],
                ],
                'meta' => [
                    'timestamp' => (new \DateTime())->format('c'),
                    'endpoint' => 'submissions',
                ],
            ]);
        } catch (\Throwable $e) {
            Craft::error('API Test Error: ' . $e->getMessage(), __METHOD__);

            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'SUBMISSIONS_FETCH_ERROR',
                    'message' => 'Failed to fetch submissions',
                    'detail' => Craft::$app->config->general->devMode ? $e->getMessage() : null,
                ],
            ]);
        }
    }
    
    /**
     * Test authentication endpoint
     * URL: /api/test/formie/auth
     * Header: X-API-Key: your-api-key-here
     */
    public function actionTestAuth(): Response
    {
        $apiKeyData = $this->apiKeyData ?? [];

        return $this->asJson([
            'success' => true,
            'data' => [
                'authenticated' => true,
                'apiKeyInfo' => [
                    'name' => $apiKeyData['name'] ?? null,
                    'permissions' => $apiKeyData['permissions'] ?? [],
                    'rateLimit' => $apiKeyData['rateLimit'] ?? null,
                ],
            ],
            'meta' => [
                'timestamp' => (new \DateTime())->format('c'),
                'endpoint' => 'auth',
            ],
        ]);
    }
}
