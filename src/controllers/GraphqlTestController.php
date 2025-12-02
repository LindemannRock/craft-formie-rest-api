<?php
/**
 * GraphQL Test Controller
 *
 * Provides test endpoints that demonstrate GraphQL queries
 * and help developers understand how to use Formie's GraphQL API
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
use GraphQL\GraphQL;
use yii\web\Response;

class GraphqlTestController extends Controller
{
    /**
     * Allow anonymous access for testing
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // Set response format to JSON
        Craft::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    /**
     * Test endpoint showing available GraphQL queries
     * GET /api/test/graphql/info
     */
    public function actionInfo(): array
    {
        return [
            'success' => true,
            'message' => 'Formie GraphQL API Information',
            'graphqlEndpoint' => '/api',
            'authentication' => [
                'method' => 'Bearer Token',
                'header' => 'Authorization: Bearer YOUR_TOKEN_HERE',
                'getToken' => 'Create in Craft CP → GraphQL → Tokens',
            ],
            'playground' => [
                'url' => '/admin/graphiql',
                'description' => 'Interactive GraphQL explorer with schema documentation',
            ],
            'availableQueries' => [
                'formieForms' => 'List all forms',
                'formieForm' => 'Get single form by handle or ID',
                'formieSubmissions' => 'List submissions with filters',
                'formieSubmission' => 'Get single submission',
            ],
            'exampleQueries' => [
                'listForms' => '{ formieForms { handle title } }',
                'getForm' => '{ formieForm(handle: "customerFeedback") { id title pages { fields { handle name type } } } }',
                'getSubmissions' => '{ formieSubmissions(form: ["customerFeedback"], limit: 10) { id dateCreated } }',
            ],
            'documentation' => '/api/test/graphql/examples',
        ];
    }

    /**
     * Show example GraphQL queries
     * GET /api/test/graphql/examples
     */
    public function actionExamples(): array
    {
        return [
            'success' => true,
            'examples' => [
                [
                    'name' => 'List All Forms',
                    'description' => 'Get basic information about all forms',
                    'query' => '
query ListForms {
  formieForms {
    id
    handle
    title
    dateCreated
  }
}',
                    'curl' => $this->getCurlExample('{ formieForms { id handle title dateCreated } }'),
                ],
                [
                    'name' => 'Get Form with Fields',
                    'description' => 'Get detailed form information including all fields',
                    'query' => '
query GetFormDetails($handle: String!) {
  formieForm(handle: $handle) {
    id
    title
    pages {
      name
      fields {
        handle
        name
        type
        required
      }
    }
  }
}',
                    'variables' => [
                        'handle' => 'customerFeedback',
                    ],
                ],
                [
                    'name' => 'Get Recent Submissions',
                    'description' => 'Get submissions from a specific form',
                    'query' => '
query GetSubmissions($formHandle: [String]!, $limit: Int) {
  formieSubmissions(form: $formHandle, limit: $limit) {
    id
    dateCreated
    ... on customerFeedback_Submission {
      customerName
      memberID
      memberEmail
    }
  }
}',
                    'variables' => [
                        'formHandle' => ['customerFeedback'],
                        'limit' => 10,
                    ],
                ],
                [
                    'name' => 'Get Submissions with Date Filter',
                    'description' => 'Filter submissions by date range',
                    'query' => '
query GetSubmissionsByDate($form: [String]!, $after: String!, $before: String!) {
  formieSubmissions(
    form: $form
    dateCreated: ["and", ">= " + $after, "<= " + $before]
  ) {
    id
    dateCreated
    fieldValues
  }
}',
                    'variables' => [
                        'form' => ['customerFeedback'],
                        'after' => '2025-01-01',
                        'before' => '2025-01-31',
                    ],
                ],
            ],
        ];
    }

    /**
     * Test GraphQL query execution
     * POST /api/test/graphql/query
     *
     * This demonstrates how to execute GraphQL queries programmatically
     */
    public function actionQuery(): array
    {
        $request = Craft::$app->request;
        $query = $request->getBodyParam('query');
        $variables = $request->getBodyParam('variables', []);
        
        if (!$query) {
            return [
                'success' => false,
                'error' => 'Query parameter is required',
                'example' => [
                    'query' => '{ formieForms { handle title } }',
                    'variables' => [],
                ],
            ];
        }

        // Check for GraphQL token
        $authHeader = $request->getHeaders()->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return [
                'success' => false,
                'error' => 'GraphQL requires Bearer token authentication',
                'help' => 'Add header: Authorization: Bearer YOUR_GRAPHQL_TOKEN',
            ];
        }

        try {
            // This is a demonstration - in production, use Craft's GraphQL controller
            return [
                'success' => true,
                'message' => 'To execute this query, send it to the main GraphQL endpoint',
                'endpoint' => '/api',
                'method' => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer YOUR_TOKEN',
                    'Content-Type' => 'application/json',
                ],
                'body' => [
                    'query' => $query,
                    'variables' => $variables,
                ],
                'note' => 'This test endpoint shows the structure but does not execute queries. Use the actual /api endpoint.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Compare REST vs GraphQL responses
     * GET /api/test/graphql/compare?formHandle=customerFeedback
     */
    public function actionCompare(): array
    {
        $formHandle = Craft::$app->request->getParam('formHandle', 'customerFeedback');
        
        return [
            'success' => true,
            'comparison' => 'REST vs GraphQL for getting form submissions',
            'scenario' => 'Get form details and last 5 submissions',
            
            'rest' => [
                'requests' => 2,
                'endpoints' => [
                    "GET /api/v1/formie/forms/{$formHandle}",
                    "GET /api/v1/formie/submissions?formHandle={$formHandle}&limit=5",
                ],
                'totalSize' => 'Approximately 5KB (depends on form complexity)',
                'pros' => [
                    'Simple to understand',
                    'Works with basic HTTP clients',
                    'Predictable response structure',
                ],
                'cons' => [
                    'Multiple requests needed',
                    'Over-fetching (gets all fields)',
                    'Fixed response structure',
                ],
            ],
            
            'graphql' => [
                'requests' => 1,
                'endpoint' => 'POST /api',
                'query' => '
query GetFormAndSubmissions($handle: String!) {
  form: formieForm(handle: $handle) {
    id
    title
    fields {
      handle
      name
    }
  }
  submissions: formieSubmissions(form: [$handle], limit: 5) {
    id
    dateCreated
    ... on customerFeedback_Submission {
      customerName
      memberID
    }
  }
}',
                'totalSize' => 'Approximately 1.5KB (only requested fields)',
                'pros' => [
                    'Single request',
                    'Get exactly what you need',
                    'Can query relationships',
                    'Schema is self-documenting',
                ],
                'cons' => [
                    'Learning curve',
                    'Requires GraphQL client/knowledge',
                    'More complex error handling',
                ],
            ],
            
            'recommendation' => 'Start with REST for simple needs, adopt GraphQL for complex queries or when minimizing requests is important.',
        ];
    }

    /**
     * Get GraphQL schema info
     * GET /api/test/graphql/schema
     */
    public function actionSchema(): array
    {
        return [
            'success' => true,
            'message' => 'GraphQL schema information',
            'schemaExplorer' => '/admin/graphiql',
            'formieTypes' => [
                'queries' => [
                    'formieForms' => [
                        'description' => 'Query multiple forms',
                        'arguments' => ['id', 'uid', 'handle', 'status', 'limit', 'offset', 'orderBy'],
                        'returns' => '[FormInterface]',
                    ],
                    'formieForm' => [
                        'description' => 'Query single form',
                        'arguments' => ['id', 'uid', 'handle'],
                        'returns' => 'FormInterface',
                    ],
                    'formieSubmissions' => [
                        'description' => 'Query multiple submissions',
                        'arguments' => ['id', 'uid', 'form', 'status', 'dateCreated', 'limit', 'offset', 'orderBy'],
                        'returns' => '[SubmissionInterface]',
                    ],
                    'formieSubmission' => [
                        'description' => 'Query single submission',
                        'arguments' => ['id', 'uid'],
                        'returns' => 'SubmissionInterface',
                    ],
                ],
                'types' => [
                    'FormInterface' => 'Form data including settings and fields',
                    'SubmissionInterface' => 'Submission data with dynamic fields based on form',
                    'FieldInterface' => 'Field configuration and properties',
                    'PageInterface' => 'Form page with fields',
                ],
                'fieldTypes' => [
                    'Field_SingleLineText',
                    'Field_MultiLineText',
                    'Field_Email',
                    'Field_Number',
                    'Field_Phone',
                    'Field_Dropdown',
                    'Field_Radio',
                    'Field_Checkboxes',
                    'Field_Date',
                    'Field_Name',
                    'Field_Address',
                    // ... and more
                ],
            ],
            'authentication' => [
                'type' => 'Bearer Token',
                'createToken' => 'Craft CP → GraphQL → Tokens',
                'permissions' => [
                    'View forms',
                    'View submissions',
                ],
            ],
        ];
    }

    /**
     * Helper to generate cURL example
     */
    private function getCurlExample(string $query): string
    {
        $payload = Json::encode(['query' => $query]);
        
        return "curl -X POST https://yoursite.com/api \\
  -H \"Authorization: Bearer YOUR_TOKEN\" \\
  -H \"Content-Type: application/json\" \\
  -d '{$payload}'";
    }
}
