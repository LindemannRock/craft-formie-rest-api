<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use Craft;
use craft\web\Response;
use lindemannrock\formierestapi\controllers\ApiTestController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\tests\Stubs\RecordingSecurityService;
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use yii\base\InlineAction;

/**
 * Dev-only diagnostics preserve their JSON payloads while reporting truthful
 * HTTP failure statuses to clients and structured access logging.
 *
 * @since 3.11.0
 */
final class ApiTestControllerStatusTest extends TestCase
{
    protected function cleanupExternalState(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%formierestapi_api_keys}}', ['like', 'name', self::MARKER . '%', false])
            ->execute();
        parent::cleanupExternalState();
    }

    public function testMissingParametersAndFormsReturn400And404(): void
    {
        $missingParameter = $this->runSubmissionsRequest([]);
        self::assertSame(400, $missingParameter['response']->statusCode);
        self::assertSame('MISSING_PARAMETER', $missingParameter['response']->data['error']['code']);
        self::assertSame(400, $missingParameter['security']->accessEvents[0]['responseCode']);

        $missingForm = $this->runSubmissionsRequest(['formHandle' => 'definitelyDoesNotExist']);
        self::assertSame(404, $missingForm['response']->statusCode);
        self::assertSame('FORM_NOT_FOUND', $missingForm['response']->data['error']['code']);
        self::assertSame(404, $missingForm['security']->accessEvents[0]['responseCode']);

        $missingFormsFilter = $this->runFormsRequest(['handle' => 'definitelyDoesNotExist']);
        self::assertSame(404, $missingFormsFilter['response']->statusCode);
        self::assertSame('NOT_FOUND', $missingFormsFilter['response']->data['error']['code']);
        self::assertSame(404, $missingFormsFilter['security']->accessEvents[0]['responseCode']);

        $missingId = $this->missingElementId();
        $missingNumericForm = $this->runFormsRequest(['id' => $missingId]);
        self::assertSame(404, $missingNumericForm['response']->statusCode);
        self::assertSame('NOT_FOUND', $missingNumericForm['response']->data['error']['code']);

        $missingNumericSubmissionForm = $this->runSubmissionsRequest(['formId' => $missingId]);
        self::assertSame(404, $missingNumericSubmissionForm['response']->statusCode);
        self::assertSame('FORM_NOT_FOUND', $missingNumericSubmissionForm['response']->data['error']['code']);
    }

    public function testCaughtOperationalFailureReturns500AndKeepsDevDetail(): void
    {
        $form = $this->seedForm();
        $this->swapPluginComponent('formie-rest-api', 'transformer', new class() extends FormieTransformerService {
            public function getFormFields(Form $form): array
            {
                throw new \RuntimeException('deterministic transformer failure');
            }
        });

        $result = $this->runFormsRequest(['handle' => $form->handle]);

        self::assertSame(500, $result['response']->statusCode);
        self::assertSame('FORMS_FETCH_ERROR', $result['response']->data['error']['code']);
        self::assertSame('deterministic transformer failure', $result['response']->data['error']['detail']);
        self::assertSame(500, $result['security']->accessEvents[0]['responseCode']);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{response: Response, security: RecordingSecurityService}
     */
    private function runFormsRequest(array $params): array
    {
        return $this->runRequest('forms', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{response: Response, security: RecordingSecurityService}
     */
    private function runSubmissionsRequest(array $params): array
    {
        return $this->runRequest('submissions', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{response: Response, security: RecordingSecurityService}
     */
    private function runRequest(string $actionId, array $params): array
    {
        [, $plaintext] = $this->seedDbKey();
        $security = new RecordingSecurityService();
        $this->swapPluginComponent('formie-rest-api', 'security', $security);
        $this->installRequestStub(new StubApiRequest(
            apiUrl: "/api/test/formie/{$actionId}",
            headers: ['X-API-Key' => $plaintext],
            apiParams: $params,
        ));
        $response = $this->installWebResponse();
        $controller = new ApiTestController('api-test', FormieRestApi::$plugin);
        $controller->enableCsrfValidation = false;
        $actionMethod = $actionId === 'forms' ? 'actionForms' : 'actionSubmissions';
        $action = new InlineAction($actionId, $controller, $actionMethod);

        self::assertTrue($controller->beforeAction($action));
        $controller->$actionMethod();
        $this->sendResponse($response);

        self::assertCount(1, $security->accessEvents);
        return ['response' => $response, 'security' => $security];
    }

    private function sendResponse(Response $response): void
    {
        ob_start();
        try {
            $response->send();
        } finally {
            ob_end_clean();
        }
    }

    /** @return array{0: ApiKey, 1: string} */
    private function seedDbKey(): array
    {
        $service = FormieRestApi::$plugin->apiKey;
        $generated = $service->generateDbKey();

        $key = new ApiKey();
        $key->name = self::MARKER . substr($generated['prefix'], 4);
        $key->keyHash = $generated['hash'];
        $key->keyPrefix = $generated['prefix'];
        $key->requireSignature = false;
        $key->allowedForms = [ApiKey::ALL_FORMS];
        self::assertTrue($key->save(), 'Test key must save: ' . print_r($key->getErrors(), true));

        return [$key, $generated['plaintext']];
    }

    private function seedForm(): Form
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API Test ', 'form');
        $form->handle = $this->nextTestMarker('formieApiTest', 'form');
        $this->saveTestElement($form);
        return $form;
    }

    private function missingElementId(): int
    {
        return (int) (new \craft\db\Query())->from('{{%elements}}')->max('id') + 1000;
    }
}
