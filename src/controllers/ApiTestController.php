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
use craft\web\Controller;
use craft\helpers\Json;
use verbb\formie\Formie;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use yii\web\Response;
use lindemannrock\formierestapi\FormieRestApi;

class ApiTestController extends Controller
{
    /**
     * Allow anonymous access for testing
     * (In production, this should require authentication)
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * Get the API key service
     */
    private function getApiKeyService()
    {
        return FormieRestApi::$plugin->apiKey;
    }

    /**
     * Validate API key from request headers
     */
    private function validateApiKey(): array|false
    {
        $apiKey = Craft::$app->request->getHeaders()->get('X-API-Key');
        return $this->getApiKeyService()->validateApiKey($apiKey);
    }

    /**
     * Check if API key has required permission
     */
    private function hasPermission(array $apiKeyData, string $permission): bool
    {
        return $this->getApiKeyService()->hasPermission($apiKeyData, $permission);
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
        // Validate API key
        $apiKeyData = $this->validateApiKey();
        if (!$apiKeyData) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid or missing API key. Please provide X-API-Key header.',
                ],
            ]);
        }
        
        // Check permissions
        if (!$this->hasPermission($apiKeyData, 'read_forms')) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'API key does not have permission to read forms.',
                ],
            ]);
        }
        
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
            
            $formData = [];
            foreach ($forms as $form) {
                // Get submission count for each form
                $submissionCount = Submission::find()
                    ->formId($form->id)
                    ->count();
                
                $formData[] = [
                    'id' => $form->id,
                    'uid' => $form->uid,
                    'handle' => $form->handle,
                    'title' => $form->title,
                    'dateCreated' => $form->dateCreated->format('c'),
                    'dateUpdated' => $form->dateUpdated->format('c'),
                    'submissionCount' => $submissionCount,
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
        // Validate API key
        $apiKeyData = $this->validateApiKey();
        if (!$apiKeyData) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid or missing API key. Please provide X-API-Key header.',
                ],
            ]);
        }
        
        // Check permissions
        if (!$this->hasPermission($apiKeyData, 'read_submissions')) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'API key does not have permission to read submissions.',
                ],
            ]);
        }
        
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
                            
                            $fieldData = [
                                'label' => $field->label,
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
                                
                                $fieldData = [
                                    'label' => $field->label,
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
        $apiKeyData = $this->validateApiKey();
        
        if (!$apiKeyData) {
            return $this->asJson([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid or missing API key. Please provide X-API-Key header.',
                ],
            ]);
        }
        
        return $this->asJson([
            'success' => true,
            'data' => [
                'authenticated' => true,
                'apiKeyInfo' => [
                    'name' => $apiKeyData['name'],
                    'permissions' => $apiKeyData['permissions'],
                    'rateLimit' => $apiKeyData['rateLimit'],
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