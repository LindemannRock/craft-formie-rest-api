<?php
/**
 * API Test Controller for Formie REST API Module
 *
 * This controller provides sample endpoints to demonstrate
 * what data the SAP team would receive from API queries
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
use craft\helpers\Json;
use craft\web\Controller;
use lindemannrock\formierestapi\FormieRestApi;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\helpers\Table as FormieTable;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class ApiTestController extends Controller
{
    /**
     * Allow anonymous access — auth is enforced via X-API-Key in beforeAction().
     * Routes are only registered when devMode is on (see FormieRestApi::init()).
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
        $apiKey = Craft::$app->request->getHeaders()->get('X-API-Key');
        $apiKeyData = FormieRestApi::$plugin->apiKey->validateApiKey($apiKey);

        if (!$apiKeyData) {
            throw new UnauthorizedHttpException('Invalid or missing API key');
        }

        // Enforce HMAC signing if the resolved key opted in via FORMIE_API_SIGNING_SECRET[_*].
        if (!empty($apiKeyData['requireSignature'])
            && !FormieRestApi::$plugin->security->validateRequestSignature($apiKeyData)
        ) {
            throw new UnauthorizedHttpException('Missing or invalid request signature');
        }

        $this->apiKeyData = $apiKeyData;

        return parent::beforeAction($action);
    }

    /**
     * Throw 403 unless the resolved key has the given permission.
     */
    private function requireApiPermission(string $permission): void
    {
        if (!FormieRestApi::$plugin->apiKey->hasPermission($this->apiKeyData ?? [], $permission)) {
            throw new ForbiddenHttpException("API key does not have permission: {$permission}");
        }
    }

    /**
     * Test endpoint to show available forms
     * URL: /api/test/formie/forms
     * URL: /api/test/formie/forms?handle=customerFeedback
     * URL: /api/test/formie/forms?id=123
     * Header: X-API-Key: test_key_sap_integration_2025
     */
    public function actionForms(): Response
    {
        $this->requireApiPermission('read_forms');

        try {
            // Get filter parameters
            $formHandle = Craft::$app->request->getParam('handle');
            $formId = Craft::$app->request->getParam('id');
            
            // Build query
            $query = Form::find()->status('enabled');
            
            // Apply filters
            if ($formHandle) {
                $query->handle($formHandle);
            } elseif ($formId) {
                $query->id($formId);
            }
            
            // Get forms
            /** @var \verbb\formie\elements\Form[] $forms */
            $forms = $query->all();

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
            // with elements to honour ElementQuery's default `dateDeleted IS NULL`).
            $countMap = [];
            $formIds = array_map(static fn(Form $f) => $f->id, $forms);
            if ($formIds) {
                $rows = (new Query())
                    ->from(['s' => FormieTable::FORMIE_SUBMISSIONS])
                    ->innerJoin(['e' => CraftTable::ELEMENTS], '[[s.id]] = [[e.id]]')
                    ->where(['e.dateDeleted' => null, 's.formId' => $formIds])
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
                    'fields' => $this->getFormFields($form),
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
                    'version' => '1.0',
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
     * Header: X-API-Key: test_key_sap_integration_2025
     */
    public function actionSubmissions(): Response
    {
        $this->requireApiPermission('read_submissions');

        $formHandle = Craft::$app->request->getParam('formHandle');
        $formId = Craft::$app->request->getParam('formId');
        $limit = Craft::$app->request->getParam('limit', 10);
        $page = Craft::$app->request->getParam('page', 1);
        $dateFrom = Craft::$app->request->getParam('dateFrom');
        $dateTo = Craft::$app->request->getParam('dateTo');
        $status = Craft::$app->request->getParam('status'); // enabled, disabled, etc.
        
        if (!$formHandle && !$formId) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_PARAMETER',
                    'message' => 'Either formHandle or formId parameter is required',
                ],
            ]);
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
                        'message' => "Form with handle '{$formHandle}' not found",
                    ],
                ]);
            }
            
            // Get submissions with pagination
            $query = Submission::find()
                ->formId($form->id)
                ->isIncomplete(false)
                ->isSpam(false)
                ->orderBy('dateCreated DESC')
                ->limit($limit);
            
            // Apply date filters
            if ($dateFrom) {
                $query->dateCreated('>= ' . $dateFrom);
            }
            if ($dateTo) {
                $query->dateCreated('<= ' . $dateTo . ' 23:59:59');
            }
            
            // Apply status filter
            if ($status) {
                $query->status($status);
            }
            
            $total = $query->count();
            $offset = ($page - 1) * $limit;
            
            /** @var \verbb\formie\elements\Submission[] $submissions */
            $submissions = $query
                ->offset($offset)
                ->all();

            $submissionData = [];
            foreach ($submissions as $submission) {
                $data = [
                    'id' => $submission->id,
                    'uid' => $submission->uid,
                    'title' => $submission->title,
                    'dateCreated' => $submission->dateCreated->format('c'),
                    'dateUpdated' => $submission->dateUpdated->format('c'),
                    'status' => $submission->getStatus(),
                    'fields' => [],
                ];
                
                // Get all field values - try direct content access first
                $content = $submission->getSerializedFieldValues();
                if (!empty($content)) {
                    // Get form fields for metadata
                    $formFields = [];
                    $form = $submission->getForm();
                    if ($form && $form->getFieldLayout()) {
                        foreach ($form->getFieldLayout()->getCustomFields() as $field) {
                            $formFields[$field->handle] = $field;
                        }
                    }
                    
                    // Process each field value
                    foreach ($content as $handle => $value) {
                        if (isset($formFields[$handle])) {
                            $field = $formFields[$handle];
                            $fieldType = basename(str_replace('\\', '/', get_class($field)));
                            
                            // Skip non-data fields (HTML, Heading, Section, etc.)
                            $skipFieldTypes = ['Html', 'Heading', 'Section', 'Summary', 'Paragraph'];
                            if (in_array($fieldType, $skipFieldTypes)) {
                                continue;
                            }
                            
                            $label = $handle;
                            if (property_exists($field, 'label') && isset($field->label)) {
                                $label = $field->label;
                            }

                            $fieldData = [
                                'label' => $label,
                                'handle' => $handle,
                                'type' => $fieldType,
                                'value' => $this->processFieldValue($field, $value),
                                'required' => $field->required,
                            ];
                            
                            // Add additional context for Rating fields
                            if ($fieldType === 'Rating' && get_class($field) === 'lindemannrock\formieratingfield\fields\Rating') {
                                $fieldData['minValue'] = (string)$field->minValue;
                                $fieldData['maxValue'] = (string)$field->maxValue;
                                $fieldData['ratingType'] = $field->ratingType; // 'star', 'emoji', or 'nps'
                            }
                            
                            $data['fields'][$handle] = $fieldData;
                        } else {
                            // Field not in layout, but has value
                            $data['fields'][$handle] = [
                                'label' => $handle,
                                'handle' => $handle,
                                'type' => 'unknown',
                                'value' => $value,
                                'required' => false,
                            ];
                        }
                    }
                } else {
                    // Fallback to field layout approach
                    $form = $submission->getForm();
                    if ($form) {
                        $fieldLayout = $form->getFieldLayout();
                        if ($fieldLayout) {
                            $fields = $fieldLayout->getCustomFields();
                            foreach ($fields as $field) {
                                $fieldType = basename(str_replace('\\', '/', get_class($field)));
                                
                                // Skip non-data fields (HTML, Heading, Section, etc.)
                                $skipFieldTypes = ['Html', 'Heading', 'Section', 'Summary', 'Paragraph'];
                                if (in_array($fieldType, $skipFieldTypes)) {
                                    continue;
                                }
                                
                                $value = $submission->getFieldValue($field->handle);

                                $label = $field->handle;
                                if (property_exists($field, 'label') && isset($field->label)) {
                                    $label = $field->label;
                                }

                                $fieldData = [
                                    'label' => $label,
                                    'handle' => $field->handle,
                                    'type' => $fieldType,
                                    'value' => $this->processFieldValue($field, $value),
                                    'required' => $field->required,
                                ];
                                
                                // Add additional context for Rating fields
                                if ($fieldType === 'Rating' && get_class($field) === 'lindemannrock\formieratingfield\fields\Rating') {
                                    $fieldData['minValue'] = (string)$field->minValue;
                                    $fieldData['maxValue'] = (string)$field->maxValue;
                                    $fieldData['ratingType'] = $field->ratingType; // 'star', 'emoji', or 'nps'
                                }
                                
                                $data['fields'][$field->handle] = $fieldData;
                            }
                        }
                    }
                }
                
                $submissionData[] = $data;
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
                        'totalPages' => ceil($total / $limit),
                        'hasMore' => ($offset + $limit) < $total,
                    ],
                ],
                'meta' => [
                    'timestamp' => (new \DateTime())->format('c'),
                    'version' => '1.0',
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
     * Header: X-API-Key: test_key_sap_integration_2025
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
                'version' => '1.0',
                'endpoint' => 'auth',
            ],
        ]);
    }
    
    /**
     * Get form fields information
     */
    private function getFormFields(Form $form): array
    {
        $fields = [];
        
        foreach ($form->getCustomFields() as $field) {
            $fields[] = [
                'handle' => $field->handle,
                'label' => $field->label,
                'type' => basename(str_replace('\\', '/', get_class($field))),
                'required' => $field->required,
                'instructions' => $field->instructions,
            ];
        }
        
        return $fields;
    }
    
    /**
     * Process field value based on type
     */
    private function processFieldValue($field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Handle different field types
        switch (get_class($field)) {
            case 'verbb\formie\fields\Number':
                return is_numeric($value) ? (float)$value : null;
                
            case 'verbb\formie\fields\Dropdown':
            case 'verbb\formie\fields\Radio':
                return is_array($value) ? ($value['value'] ?? $value[0] ?? null) : $value;
                
            case 'verbb\formie\fields\Checkboxes':
                if (is_array($value)) {
                    return array_map(function($item) {
                        return is_array($item) ? ($item['value'] ?? $item) : $item;
                    }, $value);
                }
                return $value;
                
            case 'verbb\formie\fields\Date':
                if ($value instanceof \DateTime) {
                    return $value->format('c');
                }
                return $value;
                
            case 'verbb\formie\fields\Name':
                if (is_array($value)) {
                    return [
                        'firstName' => $value['firstName'] ?? null,
                        'lastName' => $value['lastName'] ?? null,
                        'fullName' => trim(($value['firstName'] ?? '') . ' ' . ($value['lastName'] ?? '')),
                    ];
                }
                return $value;
                
            case 'verbb\formie\fields\Phone':
                if (is_array($value)) {
                    return $value['phoneNumber'] ?? $value['number'] ?? null;
                }
                return $value;
                
            case 'verbb\formie\fields\Email':
                if (is_array($value)) {
                    return $value['email'] ?? $value[0] ?? null;
                }
                return $value;
                
            case 'verbb\formie\fields\FileUpload':
                if ($value) {
                    $assets = [];
                    foreach ($value as $asset) {
                        $assets[] = [
                            'filename' => $asset->filename,
                            'url' => $asset->getUrl(),
                        ];
                    }
                    return $assets;
                }
                return null;
                
            default:
                return is_string($value) ? $value : Json::encode($value);
        }
    }
}
