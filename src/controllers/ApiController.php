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
use craft\helpers\Json;
use craft\web\Controller;
use lindemannrock\formierestapi\FormieRestApi;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\Formie;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class ApiController extends Controller
{
    /**
     * Allow anonymous access with API key authentication
     */
    protected array|int|bool $allowAnonymous = true;

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
        
        // Store API key data for use in actions
        $this->apiKeyData = $apiKeyData;

        // Set response format to JSON
        Craft::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }
    
    /**
     * @var array Current API key data
     */
    private array $apiKeyData = [];

    /**
     * Get all forms
     * GET /api/v1/formie/forms
     */
    public function actionForms(): array
    {
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
        
        $query->limit($limit)->offset($offset);
        
        // Get forms
        $forms = $query->all();
        $total = $query->count();
        
        // Format response
        $formData = [];
        foreach ($forms as $form) {
            $formData[] = $this->transformForm($form);
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
        $request = Craft::$app->request;
        
        // Get query parameters
        $formId = $request->getParam('formId');
        $formHandle = $request->getParam('formHandle');
        $status = $request->getParam('status', 'live');
        $limit = (int) $request->getParam('limit', 100);
        $offset = (int) $request->getParam('offset', 0);
        $dateFrom = $request->getParam('dateFrom');
        $dateTo = $request->getParam('dateTo');
        
        // Build query
        $query = Submission::find();
        
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
        
        // Date filters
        if ($dateFrom) {
            $query->dateCreated('>= ' . $dateFrom);
        }
        if ($dateTo) {
            $query->dateCreated('<= ' . $dateTo);
        }
        
        // Apply limit and offset
        $query->limit($limit)->offset($offset);
        
        // Order by newest first
        $query->orderBy('dateCreated DESC');
        
        // Get submissions
        $submissions = $query->all();
        $total = $query->count();
        
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
     * Transform form for API response
     */
    private function transformForm(Form $form, bool $includeFields = false): array
    {
        $data = [
            'id' => $form->id,
            'uid' => $form->uid,
            'handle' => $form->handle,
            'title' => $form->title,
            'status' => $form->status,
            'dateCreated' => $form->dateCreated->format('c'),
            'dateUpdated' => $form->dateUpdated->format('c'),
            'submissionCount' => Submission::find()->formId($form->id)->count(),
        ];
        
        if ($includeFields) {
            $data['fields'] = $this->getFormFields($form);
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
            'formId' => $form->id,
            'formHandle' => $form->handle,
            'status' => $submission->status,
            'dateCreated' => $submission->dateCreated->format('c'),
            'dateUpdated' => $submission->dateUpdated->format('c'),
            'fields' => [],
        ];
        
        // Get field values
        $content = $submission->getSerializedFieldValues();
        if (!empty($content)) {
            foreach ($content as $handle => $value) {
                $field = $form->getFieldByHandle($handle);
                if ($field) {
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
                    ];
                    
                    // Add additional context for Rating fields
                    if ($fieldType === 'Rating' && get_class($field) === 'lindemannrock\formieratingfield\fields\Rating') {
                        $fieldData['minValue'] = (string)$field->minValue;
                        $fieldData['maxValue'] = (string)$field->maxValue;
                        $fieldData['ratingType'] = $field->ratingType; // 'star', 'emoji', or 'nps'
                    }
                    
                    $data['fields'][$handle] = $fieldData;
                }
            }
        }
        
        if ($includeForm) {
            $data['form'] = $this->transformForm($form);
        }
        
        return $data;
    }

    /**
     * Get form fields
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
     * Get form pages
     */
    private function getFormPages(Form $form): array
    {
        $pages = [];
        
        foreach ($form->getPages() as $page) {
            $pageData = [
                'id' => $page->id,
                'name' => $page->name,
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

    /**
     * Process field value for output
     */
    private function processFieldValue($field, $value)
    {
        // Handle different field types
        if ($field instanceof \verbb\formie\fields\FileUpload && $value) {
            // Return asset URLs
            $assets = [];
            foreach ($value as $asset) {
                $assets[] = [
                    'filename' => $asset->filename,
                    'url' => $asset->getUrl(),
                ];
            }
            return $assets;
        }
        
        return $value;
    }
}
