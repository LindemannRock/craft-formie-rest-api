<?php
/**
 * Formie API Controller
 *
 * Provides REST API endpoints for accessing Formie forms and submissions
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
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;
use yii\web\UnauthorizedHttpException;

class ApiController extends Controller
{
    use ApiKeyScopeTrait;

    /**
     * Allow anonymous access with API key authentication
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * @var string|null The raw X-API-Key header for the current request, cached
     * in beforeAction() so afterAction() reuses it without re-reading the header.
     */
    private ?string $apiKey = null;

    /**
     * Whether this controller has registered its request-scoped access-log
     * finalizer on the current response.
     */
    private bool $accessLogFinalizerRegistered = false;

    /**
     * Prevent duplicate access events if response preparation is retried.
     */
    private bool $accessLogged = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // Establish the route's response contract and final-status logging
        // before any plugin-authored policy check can throw.
        Craft::$app->response->format = Response::FORMAT_JSON;
        $apiKey = Craft::$app->request->getHeaders()->get('X-API-Key');
        $this->apiKey = is_string($apiKey) ? $apiKey : null;
        $this->registerAccessLogFinalizer();

        // Validate API key for all actions
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

        // Track the key's last-used timestamp. The instanceof narrows the
        // mixed array value to ApiKey for static analysis (every valid key is
        // DB-backed, so dbKey is always present).
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
     * Register one callback that observes the response only after its final
     * status and payload have been prepared, including exception responses.
     */
    private function registerAccessLogFinalizer(): void
    {
        if ($this->accessLogFinalizerRegistered) {
            return;
        }

        $this->accessLogFinalizerRegistered = true;
        $response = Craft::$app->getResponse();
        $response->on(Response::EVENT_AFTER_PREPARE, function() use ($response): void {
            if ($this->accessLogged) {
                return;
            }

            $this->accessLogged = true;
            FormieRestApi::$plugin->security->logApiAccess(
                $this->apiKey ?? '',
                Craft::$app->request->getUrl(),
                Craft::$app->request->getQueryParams(),
                $response->statusCode,
            );
        });
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
     * Read a supplied integer query parameter without lossy casting.
     *
     * Omitted parameters retain their endpoint-specific default. Supplied
     * values must use integer syntax and fall within the declared range.
     */
    private function queryInteger(string $name, int $default, int $minimum, ?int $maximum = null): int
    {
        $raw = Craft::$app->request->getParam($name, null);
        if ($raw === null) {
            return $default;
        }

        if (is_int($raw)) {
            $value = $raw;
        } elseif (is_string($raw) && preg_match('/^-?\d+$/D', $raw) === 1) {
            $filtered = filter_var($raw, FILTER_VALIDATE_INT);
            if ($filtered === false) {
                throw new BadRequestHttpException("Invalid {$name}: expected an integer");
            }
            $value = $filtered;
        } else {
            throw new BadRequestHttpException("Invalid {$name}: expected an integer");
        }

        if ($value < $minimum || ($maximum !== null && $value > $maximum)) {
            $range = $maximum === null ? "{$minimum} or greater" : "between {$minimum} and {$maximum}";
            throw new BadRequestHttpException("Invalid {$name}: expected an integer {$range}");
        }

        return $value;
    }

    /**
     * Read the optional `fields` query param as a sparse-fieldset handle list.
     * Returns null (= all fields) when absent or when not in a web-request context
     * (e.g. invoked from the console/tests, where there are no query params).
     *
     * @return string[]|null
     */
    private function requestedFields(): ?array
    {
        $request = Craft::$app->getRequest();

        return $request instanceof \craft\web\Request
            ? self::parseFieldList($request->getParam('fields'))
            : null;
    }

    /**
     * Parse a `fields` query param ("a, b ,c") into a list of field handles for a
     * sparse fieldset, or null when absent/empty (meaning: return all fields).
     * Whitespace is trimmed and blank entries dropped. Unknown handles are simply
     * absent from the response (no error) — the transformer only emits handles
     * that exist on the form.
     *
     * @return string[]|null
     */
    private static function parseFieldList(mixed $param): ?array
    {
        if (!is_string($param)) {
            return null;
        }

        $handles = array_values(array_filter(
            array_map('trim', explode(',', $param)),
            static fn(string $handle): bool => $handle !== '',
        ));

        return $handles === [] ? null : $handles;
    }

    /**
     * Get all forms
     * GET /api/v1/formie/forms
     */
    public function actionForms(): array
    {
        $this->requireApiPermission('read_forms');
        $includeSubmissionCount = $this->hasApiPermission('read_submissions');

        $request = Craft::$app->request;
        
        // Get query parameters
        $limit = $this->queryInteger('limit', 100, 1, 100);
        $offset = $this->queryInteger('offset', 0, 0);
        $status = $request->getParam('status', 'enabled');
        
        // Build query
        $query = Form::find();

        if ($status !== 'all') {
            $query->status($status);
        }

        // Form-scoped keys only ever see their allowed forms
        $scoped = $this->scopedFormHandles();
        if ($scoped !== null) {
            $query->handle($scoped);
        }

        // Run COUNT against a clone before applying limit/offset — avoids
        // re-preparing the (already-executed) main query for the count.
        $total = (int) (clone $query)->count();

        $query->limit($limit)->offset($offset);

        // Get forms
        /** @var \verbb\formie\elements\Form[] $forms */
        $forms = $query->all();

        // Batch-fetch submission counts for all forms in one query (avoids N+1).
        // Joins elements to honour ElementQuery's default `dateDeleted IS NULL`,
        // and excludes drafts + spam to match the /submissions endpoint contract.
        $countMap = [];
        $formIds = array_map(static fn(Form $f) => $f->id, $forms);
        if ($includeSubmissionCount && $formIds) {
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
            $formData[] = $this->transformForm(
                $form,
                submissionCount: $includeSubmissionCount ? (int) ($countMap[$form->id] ?? 0) : null,
                includeSubmissionCount: $includeSubmissionCount,
            );
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

        $scoped = $this->scopedFormHandles();
        $query = Form::find()->id($formId);
        if ($scoped !== null) {
            $query->handle($scoped);
        }

        /** @var \verbb\formie\elements\Form|null $form */
        $form = $query->one();

        if (!$form) {
            if ($scoped !== null) {
                throw new ForbiddenHttpException('API key is not allowed to access this form');
            }
            throw new NotFoundHttpException("Form with ID {$formId} not found");
        }

        return [
            'success' => true,
            'data' => $this->transformForm(
                $form,
                includeFields: true,
                includeSubmissionCount: $this->hasApiPermission('read_submissions'),
            ),
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
        // Scope check before the lookup — an out-of-scope key gets the same
        // 403 whether or not the handle exists (no existence leak).
        $this->requireFormInScope($handle);

        /** @var \verbb\formie\elements\Form|null $form */
        $form = Form::find()->handle($handle)->one();

        if (!$form) {
            throw new NotFoundHttpException("Form with handle '{$handle}' not found");
        }

        return [
            'success' => true,
            'data' => $this->transformForm(
                $form,
                includeFields: true,
                includeSubmissionCount: $this->hasApiPermission('read_submissions'),
            ),
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
        $limit = $this->queryInteger('limit', 100, 1, 100);
        $offset = $this->queryInteger('offset', 0, 0);
        $dateFrom = $request->getParam('dateFrom');
        $dateTo = $request->getParam('dateTo');
        // Sparse fieldset — null means "all fields".
        $onlyFields = $this->requestedFields();

        // Build query — exclude abandoned drafts and Akismet-flagged spam
        // (matches the test-endpoint behaviour and the documented API contract)
        $query = Submission::find()
            ->isIncomplete(false)
            ->isSpam(false);

        // Filter by form
        if ($formHandle) {
            // Scope check before the lookup — an out-of-scope key gets the same
            // 403 whether or not the handle exists (no existence leak).
            $this->requireFormInScope($formHandle);

            $form = Form::find()->handle($formHandle)->one();
            if (!$form) {
                throw new BadRequestHttpException("Form with handle '{$formHandle}' not found");
            }
            $query->formId($form->id);
        } elseif ($formId) {
            if (($scoped = $this->scopedFormHandles()) !== null) {
                $form = Form::find()->id($formId)->handle($scoped)->one();
                if (!$form) {
                    throw new ForbiddenHttpException('API key is not allowed to access this form');
                }
            }
            $query->formId($formId);
        } elseif (($scoped = $this->scopedFormHandles()) !== null) {
            // No form filter requested: constrain a scoped key to its allowed
            // forms so it can never list other forms' submissions. `[0]` when
            // none of the allowed handles exist — formId([]) would mean
            // "no constraint" and leak everything.
            $scopedIds = Form::find()->handle($scoped)->ids();
            $query->formId($scopedIds ?: [0]);
        }
        
        // Filter by status
        if ($status !== 'all') {
            $query->status($status);
        }
        
        // Date filters — validate before passing to the query builder.
        // Craft ElementQuery's dateCreated() is a setter, NOT a chainer —
        // calling it twice replaces the first value. Combine constraints into
        // a single `['and', '>= ...', '<= ...']` array when both are set.
        $dateFromStr = $this->parseDateFilter($dateFrom, 'dateFrom', false);
        $dateToStr = $this->parseDateFilter($dateTo, 'dateTo', true);
        if ($dateFromStr !== null && $dateToStr !== null) {
            $query->dateCreated(['and', '>= ' . $dateFromStr, '<= ' . $dateToStr]);
        } elseif ($dateFromStr !== null) {
            $query->dateCreated('>= ' . $dateFromStr);
        } elseif ($dateToStr !== null) {
            $query->dateCreated('<= ' . $dateToStr);
        }
        
        // Run COUNT against a clone before applying limit/offset/order — avoids
        // re-preparing the (already-executed) main query for the count.
        $total = (int) (clone $query)->count();

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
            $submissionData[] = $this->transformSubmission($submission, false, $onlyFields);
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

        $query = Submission::find()
            ->id($submissionId)
            ->isIncomplete(false)
            ->isSpam(false);

        $scoped = $this->scopedFormHandles();
        if ($scoped !== null) {
            $scopedIds = Form::find()->handle($scoped)->ids();
            $query->formId($scopedIds ?: [0]);
        }

        /** @var \verbb\formie\elements\Submission|null $submission */
        $submission = $query->one();

        if (!$submission) {
            if ($scoped !== null) {
                throw new ForbiddenHttpException('API key is not allowed to access this form');
            }
            throw new NotFoundHttpException("Submission with ID {$submissionId} not found");
        }

        $form = $submission->getForm();
        if ($form !== null) {
            $this->requireFormInScope($form->handle);
        } elseif ($scoped !== null) {
            // Orphaned submission with no resolvable form: a scoped key has no
            // basis to claim it — fail closed.
            throw new ForbiddenHttpException('API key is not allowed to access this form');
        }

        $onlyFields = $this->requestedFields();

        return [
            'success' => true,
            'data' => $this->transformSubmission($submission, true, $onlyFields),
            'meta' => [
                'timestamp' => (new \DateTime())->format('c'),
            ],
        ];
    }

    /**
     * Transform form for API response.
     *
     * Pass `$submissionCount` when batching across many forms to avoid N+1.
     * When included but null (single-form detail endpoints), the count is queried inline.
     */
    private function transformForm(
        Form $form,
        bool $includeFields = false,
        ?int $submissionCount = null,
        bool $includeSubmissionCount = false,
    ): array {
        $data = [
            'id' => $form->id,
            'uid' => $form->uid,
            'handle' => $form->handle,
            'title' => $form->title,
            'status' => $form->status,
            'dateCreated' => $form->dateCreated->format('c'),
            'dateUpdated' => $form->dateUpdated->format('c'),
        ];

        if ($includeSubmissionCount) {
            $data['submissionCount'] = $submissionCount ?? (int) Submission::find()
                ->formId($form->id)
                ->isIncomplete(false)
                ->isSpam(false)
                ->count();
        }
        
        if ($includeFields) {
            // Form-level metadata (appearance, behaviour, privacy, restrictions)
            // — only on the detail path to keep the list endpoint lean.
            foreach (FormieRestApi::$plugin->transformer->getFormMetadata($form) as $k => $v) {
                $data[$k] = $v;
            }
            $data['fields'] = FormieRestApi::$plugin->transformer->getFormFields($form);
            $data['pages'] = $this->getFormPages($form);
        }

        return $data;
    }

    /**
     * Transform submission for API response
     */
    /**
     * @param string[]|null $onlyFields When non-null, the `fields` map includes only
     *   these field handles (sparse fieldset from the `?fields=` query param).
     */
    private function transformSubmission(Submission $submission, bool $includeForm = false, ?array $onlyFields = null): array
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
            'fields' => FormieRestApi::$plugin->transformer->transformSubmissionFields($submission, $onlyFields),
        ];

        if ($includeForm && $form !== null) {
            $data['form'] = $this->transformForm($form, includeSubmissionCount: true);
        }

        return $data;
    }

    /**
     * Get form pages with per-page settings + conditions.
     *
     * Per-page settings come from `FieldLayoutPageSettings`. We expose only
     * meaningful keys: button labels and visibility. Page-level conditions
     * (whole-page show/hide) and next-button conditions are flattened to the
     * same shape used by field conditions: `{ enabled, showRule, conditionRule, rules }`.
     */
    private function getFormPages(Form $form): array
    {
        $pages = [];

        foreach ($form->getPages() as $page) {
            $pageData = [
                'id' => $page->id,
                'label' => $page->label,
                'sortOrder' => $page->sortOrder,
            ];

            $pageSettings = $page->getPageSettings();
            if ($pageSettings !== null) {
                $settings = [];
                foreach (['submitButtonLabel', 'backButtonLabel', 'saveButtonLabel'] as $k) {
                    if (!empty($pageSettings->$k)) {
                        $settings[$k] = $pageSettings->$k;
                    }
                }
                if ($pageSettings->showBackButton) {
                    $settings['showBackButton'] = true;
                }
                if ($pageSettings->showSaveButton) {
                    $settings['showSaveButton'] = true;
                }
                if ($settings !== []) {
                    $pageData['settings'] = $settings;
                }

                if ($pageSettings->enablePageConditions) {
                    $flat = $this->flattenPageConditions($pageSettings->pageConditions);
                    if ($flat !== null) {
                        $pageData['conditions'] = $flat;
                    }
                }

                if ($pageSettings->enableNextButtonConditions) {
                    $flat = $this->flattenPageConditions($pageSettings->nextButtonConditions);
                    if ($flat !== null) {
                        $pageData['nextButtonConditions'] = $flat;
                    }
                }
            }

            $pageData['fields'] = [];
            foreach ($page->getFields() as $field) {
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

    /**
     * Flatten Formie's nested `{ showRule, conditionRule, conditions: [...] }`
     * page-condition shape into a single-level `{ enabled, showRule, conditionRule, rules }`.
     *
     * @param array<string, mixed>|null $conditions
     * @return array<string, mixed>|null
     */
    private function flattenPageConditions(?array $conditions): ?array
    {
        if (!is_array($conditions) || $conditions === []) {
            return null;
        }
        $flat = ['enabled' => true];
        if (isset($conditions['showRule'])) {
            $flat['showRule'] = $conditions['showRule'];
        }
        if (isset($conditions['conditionRule'])) {
            $flat['conditionRule'] = $conditions['conditionRule'];
        }
        if (isset($conditions['conditions']) && is_array($conditions['conditions'])) {
            $flat['rules'] = $conditions['conditions'];
        }
        return $flat;
    }
}
