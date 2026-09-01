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
use craft\web\Controller;
use craft\web\Response;
use lindemannrock\formierestapi\controllers\ApiController;
use lindemannrock\formierestapi\controllers\ApiTestController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\services\SecurityService;
use lindemannrock\formierestapi\tests\Stubs\RecordingSecurityService;
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use yii\base\InlineAction;
use yii\web\ForbiddenHttpException;
use yii\web\TooManyRequestsHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Public and dev REST controllers establish JSON before policy checks and log
 * the final HTTP status exactly once after response preparation.
 *
 * @since 3.11.0
 */
final class ApiControllerRequestLifecycleTest extends TestCase
{
    protected function cleanupExternalState(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%formierestapi_api_keys}}', ['like', 'name', self::MARKER . '%', false])
            ->execute();
        parent::cleanupExternalState();
    }

    public function testEarlyAuthenticationFailuresRemainJsonAndAreLoggedByBothControllers(): void
    {
        foreach ([ApiController::class, ApiTestController::class] as $controllerClass) {
            $security = $this->installRecordingSecurity();
            $this->installRequestStub(new StubApiRequest(headers: []));
            $response = $this->installWebResponse();
            $controller = new $controllerClass('api', FormieRestApi::$plugin);
            $controller->enableCsrfValidation = false;

            try {
                $controller->beforeAction($this->action($controller));
                self::fail('A missing API key must be rejected.');
            } catch (UnauthorizedHttpException $e) {
                self::assertSame(401, $e->statusCode);
                self::assertSame(Response::FORMAT_JSON, $response->format);
                $response->statusCode = $e->statusCode;
                $response->data = ['message' => $e->getMessage()];
            }

            $body = $this->sendResponse($response);

            self::assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));
            self::assertSame('Invalid or missing API key', json_decode($body, true, flags: JSON_THROW_ON_ERROR)['message']);
            self::assertCount(1, $security->accessEvents);
            self::assertSame('', $security->accessEvents[0]['apiKey']);
            self::assertSame(401, $security->accessEvents[0]['responseCode']);

            $response->send();
            self::assertCount(1, $security->accessEvents, 'A response can only produce one access event.');
        }
    }

    public function testSuccessfulAndRateLimitedRequestsLogTheirFinalStatusOnce(): void
    {
        [, $plaintext] = $this->seedDbKey(['requireSignature' => false]);

        foreach ([true, false] as $allowed) {
            $security = $this->installRecordingSecurity();
            $security->rateAllowed = $allowed;
            $this->installRequestStub(new StubApiRequest(headers: ['X-API-Key' => $plaintext]));
            $response = $this->installWebResponse();
            $controller = new ApiController('api', FormieRestApi::$plugin);
            $controller->enableCsrfValidation = false;

            try {
                self::assertTrue($controller->beforeAction($this->action($controller)));
                $response->data = ['success' => true];
            } catch (TooManyRequestsHttpException $e) {
                self::assertFalse($allowed);
                $response->statusCode = $e->statusCode;
                $response->data = ['message' => $e->getMessage()];
            }

            $this->sendResponse($response);

            self::assertCount(1, $security->accessEvents);
            self::assertSame($allowed ? 200 : 429, $security->accessEvents[0]['responseCode']);
            self::assertSame($plaintext, $security->accessEvents[0]['apiKey']);
        }
    }

    public function testUnrecoverableRequiredSecretStillInvokesControllerSignaturePolicy(): void
    {
        [$key, $plaintext] = $this->seedDbKey();
        Craft::$app->getDb()->createCommand()
            ->update('{{%formierestapi_api_keys}}', ['signingSecretEnc' => base64_encode('tampered')], ['id' => $key->id])
            ->execute();

        foreach ([ApiController::class, ApiTestController::class] as $controllerClass) {
            $security = $this->installRecordingSecurity();
            $security->signatureValid = false;
            $this->installRequestStub(new StubApiRequest(headers: ['X-API-Key' => $plaintext]));
            $response = $this->installWebResponse();
            $controller = new $controllerClass('api', FormieRestApi::$plugin);
            $controller->enableCsrfValidation = false;

            try {
                $controller->beforeAction($this->action($controller));
                self::fail('A configured signature requirement must not disappear when its secret cannot decrypt.');
            } catch (UnauthorizedHttpException $e) {
                self::assertSame('Missing or invalid request signature', $e->getMessage());
                $response->statusCode = $e->statusCode;
                $response->data = ['message' => $e->getMessage()];
            }

            self::assertSame(1, $security->signatureChecks);
            $this->sendResponse($response);
            self::assertSame(401, $security->accessEvents[0]['responseCode']);
        }
    }

    public function testRequiredSignatureKeysProceedWhenControllerValidationSucceeds(): void
    {
        [, $plaintext] = $this->seedDbKey();

        foreach ([ApiController::class, ApiTestController::class] as $controllerClass) {
            $security = $this->installRecordingSecurity();
            $this->installRequestStub(new StubApiRequest(headers: ['X-API-Key' => $plaintext]));
            $response = $this->installWebResponse();
            $controller = new $controllerClass('api', FormieRestApi::$plugin);
            $controller->enableCsrfValidation = false;

            self::assertTrue($controller->beforeAction($this->action($controller)));
            self::assertSame(1, $security->signatureChecks);
            $response->data = ['success' => true];
            $this->sendResponse($response);
            self::assertSame(200, $security->accessEvents[0]['responseCode']);
        }
    }

    public function testStructuredAccessLogNeverContainsThePlaintextApiKey(): void
    {
        $apiKey = 'fra_12345678' . str_repeat('abcdef', 10);
        $logger = Craft::getLogger();
        $messageCount = count($logger->messages);
        $service = new SecurityService();
        $this->installRequestStub(new StubApiRequest());

        $service->logApiAccess($apiKey, '/api/v1/formie/forms', [], 200);
        $service->logApiAccess('', '/api/v1/formie/forms', [], 401);

        $messages = array_slice($logger->messages, $messageCount);
        self::assertCount(2, $messages);
        self::assertIsString($messages[0][0]);
        self::assertStringNotContainsString($apiKey, $messages[0][0]);
        self::assertStringContainsString(substr($apiKey, 0, 10) . '...', $messages[0][0]);
        self::assertIsString($messages[1][0]);
        self::assertStringContainsString('(missing)', $messages[1][0]);
    }

    public function testCapabilityRejectionAndUnexpectedActionFailureLog403And500(): void
    {
        [, $formsOnlyKey] = $this->seedDbKey([
            'requireSignature' => false,
            'canReadSubmissions' => false,
        ]);
        $forbiddenSecurity = $this->installRecordingSecurity();
        $this->installRequestStub(new StubApiRequest(headers: ['X-API-Key' => $formsOnlyKey]));
        $forbiddenResponse = $this->installWebResponse();
        $forbiddenController = new ApiController('api', FormieRestApi::$plugin);
        $forbiddenController->enableCsrfValidation = false;
        self::assertTrue($forbiddenController->beforeAction($this->action($forbiddenController)));

        try {
            $forbiddenController->actionSubmissions();
            self::fail('A forms-only key must not read submissions.');
        } catch (ForbiddenHttpException $e) {
            $forbiddenResponse->statusCode = $e->statusCode;
            $forbiddenResponse->data = ['message' => $e->getMessage()];
        }
        $this->sendResponse($forbiddenResponse);
        self::assertSame(403, $forbiddenSecurity->accessEvents[0]['responseCode']);

        $form = $this->seedForm();
        [, $fullKey] = $this->seedDbKey(['requireSignature' => false]);
        $this->swapPluginComponent('formie-rest-api', 'transformer', new class() extends FormieTransformerService {
            public function getFormFields(Form $form): array
            {
                throw new \RuntimeException('deterministic production failure');
            }
        });
        $failureSecurity = $this->installRecordingSecurity();
        $this->installRequestStub(new StubApiRequest(headers: ['X-API-Key' => $fullKey]));
        $failureResponse = $this->installWebResponse();
        $failureController = new ApiController('api', FormieRestApi::$plugin);
        $failureController->enableCsrfValidation = false;
        self::assertTrue($failureController->beforeAction($this->action($failureController)));

        try {
            $failureController->actionFormDetail((int) $form->id);
            self::fail('The controlled transformer failure must reach the response error lifecycle.');
        } catch (\RuntimeException $e) {
            $failureResponse->statusCode = 500;
            $failureResponse->data = ['message' => $e->getMessage()];
        }
        $this->sendResponse($failureResponse);
        self::assertSame(500, $failureSecurity->accessEvents[0]['responseCode']);
    }

    private function installRecordingSecurity(): RecordingSecurityService
    {
        $security = new RecordingSecurityService();
        $this->swapPluginComponent('formie-rest-api', 'security', $security);
        return $security;
    }

    private function action(Controller $controller): InlineAction
    {
        return new InlineAction('forms', $controller, 'actionForms');
    }

    private function sendResponse(Response $response): string
    {
        $body = '';
        ob_start();
        try {
            $response->send();
            $body = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        return is_string($body) ? $body : '';
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array{0: ApiKey, 1: string}
     */
    private function seedDbKey(array $attributes = []): array
    {
        $service = FormieRestApi::$plugin->apiKey;
        $generated = $service->generateDbKey();
        $secret = $service->generateSigningSecret();

        $key = new ApiKey();
        $key->name = self::MARKER . substr($generated['prefix'], 4);
        $key->keyHash = $generated['hash'];
        $key->keyPrefix = $generated['prefix'];
        $key->signingSecretEnc = $service->encryptSigningSecret($secret);
        $key->allowedForms = [ApiKey::ALL_FORMS];

        foreach ($attributes as $name => $value) {
            $key->$name = $value;
        }

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
}
