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
use craft\console\Application as ConsoleApplication;
use craft\elements\User;
use craft\web\Request;
use craft\web\Session;
use craft\web\View;
use lindemannrock\formierestapi\controllers\ApiKeysController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use yii\log\Logger;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Pins API-key CP capability projection, direct gates, expiry validation,
 * Formie delegation scope, and the authenticated one-request secret reveal.
 *
 * @since 3.10.2
 */
final class ApiKeysControlPanelTest extends TestCase
{
    private const ROW_PREFIX = 'fra-cp-test-';
    private const FLASH_KEY = 'fra.apiKey.newPlaintext';
    private const FLASH_SECRET = 'fra.apiKey.newSecret';

    private ?ConsoleApplication $originalApplication = null;
    private mixed $originalRequest = null;
    private mixed $originalResponse = null;
    private mixed $originalUser = null;
    private mixed $originalTwigCurrentUser = null;
    private bool $twigCurrentUserCaptured = false;
    private ApiKeysTestApplication $testApplication;
    private RecordingSession $session;
    private Form $allowedForm;
    private ApiKey $apiKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purgeKeys();

        $this->allowedForm = $this->seedForm('allowed');
        $this->seedForm('other');
        $this->apiKey = $this->seedKey('primary', [$this->allowedForm->handle]);

        $application = Craft::$app;
        self::assertInstanceOf(ConsoleApplication::class, $application);
        $this->originalApplication = $application;
        $this->originalRequest = $application->get('request');
        $this->originalResponse = $application->get('response');
        $this->originalUser = $application->get('user');
        $this->session = new RecordingSession();

        $this->testApplication = new ApiKeysTestApplication($application, $this->session);
        Craft::$app = $this->testApplication;
        \Yii::$app = $this->testApplication;
        $application->set('request', new Request([
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
        ]));
        $application->set('response', new \craft\web\Response());
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    protected function tearDown(): void
    {
        $this->purgeKeys();

        if ($this->originalApplication !== null) {
            Craft::$app = $this->originalApplication;
            \Yii::$app = $this->originalApplication;
            $this->originalApplication->set('request', $this->originalRequest);
            $this->originalApplication->set('response', $this->originalResponse);
            $this->originalApplication->set('user', $this->originalUser);
        }
        if ($this->twigCurrentUserCaptured) {
            Craft::$app->getView()->getTwig()->addGlobal('currentUser', $this->originalTwigCurrentUser);
        }
        unset($_SERVER['REQUEST_METHOD']);

        parent::tearDown();
    }

    public function testPermissionMatrixProjectsExactTableControlsAndDirectRouteGates(): void
    {
        $manage = 'formieRestApi:manageApiKeys';
        $create = 'formieRestApi:createApiKeys';
        $edit = 'formieRestApi:editApiKeys';
        $revoke = 'formieRestApi:revokeApiKeys';
        $cases = [
            'unauthorized' => [[], false, false, false, false],
            'manage-only' => [[$manage], true, false, false, false],
            'manage-create' => [[$manage, $create], true, true, false, false],
            'manage-edit' => [[$manage, $edit], true, false, true, false],
            'manage-revoke' => [[$manage, $revoke], true, false, false, true],
            'manage-create-edit' => [[$manage, $create, $edit], true, true, true, false],
            'manage-edit-revoke' => [[$manage, $edit, $revoke], true, false, true, true],
            'full' => [[$manage, $create, $edit, $revoke], true, true, true, true],
        ];

        foreach ($cases as $label => [$permissions, $canList, $canCreate, $canEdit, $canRevoke]) {
            $this->actWithPermissions($permissions, $label, true);
            $controller = $this->controller();

            if (!$canList) {
                $this->assertForbidden(fn() => $controller->requireIndexPermission(), $label . ' index');
            } else {
                $html = $this->renderCaptured($controller->actionIndex());
                $this->assertIndexCapabilities($html, $canCreate, $canEdit, $canRevoke, $label);
            }

            $this->assertGetGate($controller, null, $canCreate, $label . ' create GET');
            $this->assertGetGate($controller, (int)$this->apiKey->id, $canEdit, $label . ' edit GET');
            $this->assertPostGates($controller, $canCreate, $canEdit, $canRevoke, $label);
        }
    }

    public function testFormieSubmissionScopeFiltersListingAndBlocksBroaderDirectOperations(): void
    {
        $wildcard = $this->seedKey('wildcard', [ApiKey::ALL_FORMS]);
        $permissions = [
            'formieRestApi:manageApiKeys',
            'formieRestApi:editApiKeys',
            'formieRestApi:revokeApiKeys',
        ];

        $this->actWithPermissions(
            array_merge($permissions, ["formie-viewSubmissions:{$this->allowedForm->uid}"]),
            'limited-form',
            false,
        );
        $controller = $this->controller();
        $html = $this->renderCaptured($controller->actionIndex());
        self::assertStringContainsString($this->apiKey->name, $html);
        self::assertStringNotContainsString($wildcard->name, $html);
        self::assertSame(
            (int)$this->apiKey->id,
            $controller->actionEdit((int)$this->apiKey->id)->data['variables']['apiKey']->id ?? null,
        );
        $this->assertForbidden(
            fn(): Response => $controller->actionEdit((int)$wildcard->id),
            'limited operator broader edit',
        );
        $this->setPostBody($this->validBody($wildcard->name, [
            'keyId' => $wildcard->id,
            'allowedForms' => [ApiKey::ALL_FORMS],
        ]));
        $this->assertForbidden(fn() => $controller->actionSave(), 'limited operator broader save');
        $this->setPostBody(['ids' => [$wildcard->id]]);
        $this->assertForbidden(fn() => $controller->actionBulkDisable(), 'limited operator broader bulk status');
        $this->setPostBody(['keyId' => $wildcard->id]);
        $this->assertForbidden(fn() => $controller->actionDelete(), 'limited operator broader revoke');
        $this->setPostBody(['ids' => [$wildcard->id]]);
        $this->assertForbidden(fn() => $controller->actionBulkDelete(), 'limited operator broader bulk revoke');
        self::assertNotNull(ApiKey::findById((int)$wildcard->id));

        $this->actWithPermissions(array_merge($permissions, ['formie-viewSubmissions']), 'global-formie', false);
        $globalController = $this->controller();
        $globalHtml = $this->renderCaptured($globalController->actionIndex());
        self::assertStringContainsString($wildcard->name, $globalHtml);
        self::assertSame(
            (int)$wildcard->id,
            $globalController->actionEdit((int)$wildcard->id)->data['variables']['apiKey']->id ?? null,
        );
    }

    public function testMalformedExpiryIsRejectedWithoutCreateOrEditAndRawValueIsRedisplayed(): void
    {
        $this->actWithPermissions([
            'formieRestApi:manageApiKeys',
            'formieRestApi:createApiKeys',
            'formieRestApi:editApiKeys',
        ], 'invalid-expiry', true);

        foreach ([
            'unparseable string' => 'not-a-date',
            'malformed value' => 12345,
            'malformed array value' => ['date' => []],
            'unexpected array shape' => ['year' => '2030'],
        ] as $label => $raw) {
            $name = self::ROW_PREFIX . 'invalid-' . preg_replace('/[^a-z]+/', '-', $label);
            $this->setPostBody($this->validBody($name, ['validUntil' => $raw]));

            self::assertNull($this->controller()->actionSave(), $label);
            self::assertNull($this->keyByName($name), $label);
            $routeParams = Craft::$app->getUrlManager()->getRouteParams();
            self::assertSame($raw, $routeParams['validUntilValue'] ?? null, $label);
            self::assertNotEmpty($routeParams['apiKey']->getErrors('validUntil') ?? [], $label);
            if ($label === 'unparseable string') {
                $html = $this->renderCaptured($this->controller()->actionEdit(
                    null,
                    $routeParams['apiKey'],
                    $routeParams['validUntilValue'],
                ));
                self::assertStringContainsString('not-a-date', $html);
            }
        }

        $before = ApiKey::findById((int)$this->apiKey->id);
        self::assertNotNull($before);
        $this->setPostBody($this->validBody('should-not-persist', [
            'keyId' => $this->apiKey->id,
            'validUntil' => 'still-not-a-date',
        ]));
        self::assertNull($this->controller()->actionSave());
        $after = ApiKey::findById((int)$this->apiKey->id);
        self::assertNotNull($after);
        self::assertSame($before->name, $after->name);
        self::assertSame('still-not-a-date', Craft::$app->getUrlManager()->getRouteParams()['validUntilValue'] ?? null);
    }

    public function testAuthorizedBulkStatusAndSingleAndBulkRevokeReturnExpectedResults(): void
    {
        $this->actWithPermissions([
            'formieRestApi:manageApiKeys',
            'formieRestApi:editApiKeys',
            'formieRestApi:revokeApiKeys',
        ], 'positive-mutations', true);
        $statusKey = $this->seedKey('status-result', [$this->allowedForm->handle]);
        $singleRevokeKey = $this->seedKey('single-revoke-result', [$this->allowedForm->handle]);
        $bulkRevokeKey = $this->seedKey('bulk-revoke-result', [$this->allowedForm->handle]);
        $controller = $this->controller();

        $this->setPostBody(['ids' => [$statusKey->id]]);
        self::assertSame(1, $controller->actionBulkDisable()?->data['count'] ?? null);
        $disabled = ApiKey::findById((int)$statusKey->id);
        self::assertNotNull($disabled);
        self::assertFalse($disabled->enabled);

        $this->setPostBody(['ids' => [$statusKey->id]]);
        self::assertSame(1, $controller->actionBulkEnable()?->data['count'] ?? null);
        self::assertTrue(ApiKey::findById((int)$statusKey->id)?->enabled);

        $this->setPostBody(['keyId' => $singleRevokeKey->id]);
        self::assertTrue($controller->actionDelete()?->data['success'] ?? false);
        self::assertNull(ApiKey::findById((int)$singleRevokeKey->id));

        $this->setPostBody(['ids' => [$bulkRevokeKey->id]]);
        self::assertSame(1, $controller->actionBulkDelete()?->data['count'] ?? null);
        self::assertNull(ApiKey::findById((int)$bulkRevokeKey->id));
        self::assertNotNull(ApiKey::findById((int)$this->apiKey->id));
    }

    public function testValidAndBlankExpiryValuesPreserveSupportedSemantics(): void
    {
        $this->actWithPermissions([
            'formieRestApi:manageApiKeys',
            'formieRestApi:createApiKeys',
            'formieRestApi:editApiKeys',
        ], 'valid-expiry', true);

        $name = self::ROW_PREFIX . 'valid-array';
        $this->setPostBody($this->validBody($name, [
            'validUntil' => [
                'date' => '2031-03-04',
                'time' => '13:45',
                'timezone' => 'Asia/Dubai',
            ],
        ]));
        self::assertInstanceOf(Response::class, $this->controller()->actionSave());
        $created = $this->keyByName($name);
        self::assertNotNull($created);
        self::assertNotNull($created->validUntil);
        self::assertSame(1930383900, $created->validUntil->getTimestamp());

        $this->setPostBody($this->validBody($name, [
            'keyId' => $created->id,
            'validUntil' => '2032-05-06T07:08:00+04:00',
        ]));
        self::assertInstanceOf(Response::class, $this->controller()->actionSave());
        $updated = ApiKey::findById((int)$created->id);
        self::assertNotNull($updated?->validUntil);
        self::assertSame(1967425680, $updated->validUntil->getTimestamp());

        $this->setPostBody($this->validBody($name, [
            'keyId' => $created->id,
            'validUntil' => ['date' => '', 'time' => '', 'timezone' => 'Asia/Dubai'],
        ]));
        self::assertInstanceOf(Response::class, $this->controller()->actionSave());
        self::assertNull(ApiKey::findById((int)$created->id)?->validUntil);
    }

    public function testCreateOnlyCredentialRevealIsAuthenticatedImmediateAndAbsentFromPluginStorageAndLogs(): void
    {
        $this->actWithPermissions([
            'formieRestApi:manageApiKeys',
            'formieRestApi:createApiKeys',
        ], 'create-reveal', true);
        $name = self::ROW_PREFIX . 'reveal';
        $this->setPostBody($this->validBody($name));

        $logger = Craft::getLogger();
        $originalMessages = $logger->messages;
        $originalFlushInterval = $logger->flushInterval;
        $logger->flushInterval = 0;

        try {
            $response = $this->controller()->actionSave();
            $appendedMessages = array_slice(array_values($logger->messages), count($originalMessages));
        } finally {
            $logger->messages = $originalMessages;
            $logger->flushInterval = $originalFlushInterval;
        }

        self::assertSame($originalMessages, $logger->messages);
        self::assertSame($originalFlushInterval, $logger->flushInterval);
        self::assertInstanceOf(Response::class, $response);
        self::assertStringEndsWith('/formie-rest-api/api-keys/create', (string)$response->getHeaders()->get('Location'));
        $plaintext = $this->session->peek(self::FLASH_KEY);
        $secret = $this->session->peek(self::FLASH_SECRET);
        self::assertIsString($plaintext);
        self::assertIsString($secret);

        $stored = Craft::$app->getDb()->createCommand(
            'SELECT * FROM {{%formierestapi_api_keys}} WHERE [[name]] = :name',
            [':name' => $name],
        )->queryOne();
        self::assertIsArray($stored);
        self::assertStringNotContainsString($plaintext, json_encode($stored, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString($secret, json_encode($stored, JSON_THROW_ON_ERROR));

        $saveEvents = array_values(array_filter(
            $appendedMessages,
            static fn(mixed $message): bool => is_array($message)
                && ($message[1] ?? null) === Logger::LEVEL_INFO
                && ($message[2] ?? null) === 'formie-rest-api'
                && is_string($message[0] ?? null)
                && str_starts_with($message[0], 'API key saved | '),
        ));
        self::assertCount(1, $saveEvents);
        $saveMessage = $saveEvents[0][0] ?? null;
        self::assertIsString($saveMessage);
        self::assertStringContainsString($name, $saveMessage);
        self::assertStringContainsString((string)$stored['keyPrefix'], $saveMessage);
        self::assertStringNotContainsString($plaintext, $saveMessage);
        self::assertStringNotContainsString($secret, $saveMessage);

        $firstReveal = $this->controller()->actionEdit();
        self::assertSame($plaintext, $firstReveal->data['variables']['newPlaintext'] ?? null);
        self::assertSame($secret, $firstReveal->data['variables']['newSecret'] ?? null);
        self::assertFalse($this->session->hasFlash(self::FLASH_KEY));
        self::assertFalse($this->session->hasFlash(self::FLASH_SECRET));

        $secondReveal = $this->controller()->actionEdit();
        self::assertNull($secondReveal->data['variables']['newPlaintext'] ?? null);
        self::assertNull($secondReveal->data['variables']['newSecret'] ?? null);

        $this->session->setFlash(self::FLASH_KEY, 'preserved-key');
        $this->session->setFlash(self::FLASH_SECRET, 'preserved-secret');
        $this->actWithPermissions(['formieRestApi:manageApiKeys'], 'unauthorized-reveal', true);
        $this->assertForbidden(fn(): Response => $this->controller()->actionEdit(), 'unauthorized reveal');
        self::assertSame('preserved-key', $this->session->peek(self::FLASH_KEY));
        self::assertSame('preserved-secret', $this->session->peek(self::FLASH_SECRET));
    }

    private function controller(): CapturingApiKeysController
    {
        return new CapturingApiKeysController('api-keys', FormieRestApi::$plugin);
    }

    /** @param list<string> $permissions */
    private function actWithPermissions(array $permissions, string $suffix, bool $addGlobalFormie): void
    {
        self::assertNotNull($this->originalApplication);
        Craft::$app = $this->originalApplication;
        \Yii::$app = $this->originalApplication;
        $user = $this->createTestUser(self::ROW_PREFIX . $suffix);
        if ($addGlobalFormie) {
            $permissions[] = 'formie-viewSubmissions';
        }
        $this->grantPermissions($user, array_values(array_unique(array_merge(['accessCp'], $permissions))));
        $consoleUser = new ExplicitPermissionConsoleUser($permissions);
        $consoleUser->setIdentity($user);
        $this->originalApplication->set('user', $consoleUser);
        Craft::$app = $this->testApplication;
        \Yii::$app = $this->testApplication;

        $twig = Craft::$app->getView()->getTwig();
        if (!$this->twigCurrentUserCaptured) {
            $this->originalTwigCurrentUser = $twig->getGlobals()['currentUser'] ?? null;
            $this->twigCurrentUserCaptured = true;
        }
        $twig->addGlobal('currentUser', $user);
    }

    private function renderCaptured(Response $response): string
    {
        $template = $response->data['template'] ?? null;
        $variables = $response->data['variables'] ?? null;
        self::assertIsString($template);
        self::assertIsArray($variables);
        $identity = Craft::$app->getUser()->getIdentity();
        self::assertInstanceOf(User::class, $identity);

        return Craft::$app->getView()->renderTemplate(
            $template,
            array_merge($variables, ['currentUser' => $identity]),
            View::TEMPLATE_MODE_CP,
        );
    }

    private function assertIndexCapabilities(
        string $html,
        bool $canCreate,
        bool $canEdit,
        bool $canRevoke,
        string $label,
    ): void {
        self::assertSame($canCreate, str_contains($html, 'formie-rest-api/api-keys/create'), $label . ' new button');
        self::assertSame($canEdit, str_contains($html, 'formie-rest-api/api-keys/edit/' . $this->apiKey->id), $label . ' name/edit link');
        self::assertSame($canRevoke, str_contains($html, 'data-action="revoke"'), $label . ' row revoke');

        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        self::assertTrue($dom->loadHTML($html));
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($dom);
        self::assertSame($canEdit ? 1 : 0, $xpath->query('//*[@id="lr-bulk-enable-action"]')->length, $label . ' bulk enable');
        self::assertSame($canEdit ? 1 : 0, $xpath->query('//*[@id="lr-bulk-disable-action"]')->length, $label . ' bulk disable');
        self::assertSame($canRevoke ? 1 : 0, $xpath->query('//*[@id="lr-bulk-revoke-btn"]')->length, $label . ' bulk revoke');
        self::assertSame($canEdit || $canRevoke ? 1 : 0, $xpath->query('//th[contains(@class,"selectallcontainer")]')->length, $label . ' checkbox column');
        self::assertSame($canEdit || $canRevoke ? 1 : 0, $xpath->query('//th[normalize-space()="Actions"]')->length, $label . ' actions column');
        self::assertSame(0, $xpath->query('//th[@data-column="id"]')->length, $label . ' ID column');
    }

    private function assertGetGate(CapturingApiKeysController $controller, ?int $keyId, bool $allowed, string $label): void
    {
        if (!$allowed) {
            $this->assertForbidden(fn(): Response => $controller->actionEdit($keyId), $label);
            return;
        }

        $response = $controller->actionEdit($keyId);
        self::assertSame('formie-rest-api/api-keys/edit', $response->data['template'] ?? null, $label);
        self::assertSame($keyId === null, $response->data['variables']['isNew'] ?? null, $label);
    }

    private function assertPostGates(
        CapturingApiKeysController $controller,
        bool $canCreate,
        bool $canEdit,
        bool $canRevoke,
        string $label,
    ): void {
        $this->setPostBody($this->validBody(self::ROW_PREFIX . 'matrix-create', ['validUntil' => 'invalid']));
        $this->assertPermissionGate(fn(): ?Response => $controller->actionSave(), $canCreate, $label . ' create POST');

        $this->setPostBody($this->validBody($this->apiKey->name, [
            'keyId' => $this->apiKey->id,
            'validUntil' => 'invalid',
        ]));
        $this->assertPermissionGate(fn(): ?Response => $controller->actionSave(), $canEdit, $label . ' edit POST');

        foreach ([
            'enable' => [fn(): ?Response => $controller->actionBulkEnable(), $canEdit],
            'disable' => [fn(): ?Response => $controller->actionBulkDisable(), $canEdit],
            'bulk revoke' => [fn(): ?Response => $controller->actionBulkDelete(), $canRevoke],
            'single revoke' => [function() use ($controller): ?Response {
                try {
                    return $controller->actionDelete();
                } catch (NotFoundHttpException) {
                    return null;
                }
            }, $canRevoke],
        ] as $operation => [$action, $allowed]) {
            $this->setPostBody(['ids' => []]);
            $this->assertPermissionGate($action, $allowed, $label . ' ' . $operation);
        }
    }

    private function assertPermissionGate(callable $action, bool $allowed, string $label): void
    {
        try {
            $action();
            self::assertTrue($allowed, $label);
        } catch (ForbiddenHttpException) {
            self::assertFalse($allowed, $label);
        }
    }

    private function assertForbidden(callable $action, string $label): void
    {
        try {
            $action();
            self::fail($label . ' should be forbidden.');
        } catch (ForbiddenHttpException) {
            self::addToAssertionCount(1);
        }
    }

    /** @param array<string, mixed> $overrides */
    private function validBody(string $name, array $overrides = []): array
    {
        return array_merge([
            'name' => $name,
            'enabled' => true,
            'requireSignature' => true,
            'canReadSubmissions' => true,
            'allowedForms' => [$this->allowedForm->handle],
            'ipWhitelist' => '',
            'rateLimit' => '',
            'validUntil' => '',
        ], $overrides);
    }

    /** @param array<string, mixed> $body */
    private function setPostBody(array $body): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Craft::$app->getUrlManager()->setRouteParams([], false);
        Craft::$app->getRequest()->setBodyParams($body);
        Craft::$app->getRequest()->getHeaders()->set('Accept', 'application/json');
    }

    private function seedForm(string $suffix): Form
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API CP ', $suffix);
        $form->handle = $this->nextTestMarker('fraCpForm', $suffix);
        $this->saveTestElement($form);

        return $form;
    }

    /** @param string[] $allowedForms */
    private function seedKey(string $suffix, array $allowedForms): ApiKey
    {
        $generated = FormieRestApi::$plugin->apiKey->generateDbKey();
        $secret = FormieRestApi::$plugin->apiKey->generateSigningSecret();
        $key = new ApiKey();
        $key->name = self::ROW_PREFIX . $suffix;
        $key->keyHash = $generated['hash'];
        $key->keyPrefix = $generated['prefix'];
        $key->signingSecretEnc = FormieRestApi::$plugin->apiKey->encryptSigningSecret($secret);
        $key->allowedForms = $allowedForms;
        self::assertTrue($key->save(), json_encode($key->getErrors()));

        return $key;
    }

    private function keyByName(string $name): ?ApiKey
    {
        foreach (ApiKey::findAll() as $key) {
            if ($key->name === $name) {
                return $key;
            }
        }

        return null;
    }

    private function purgeKeys(): void
    {
        Craft::$app->getDb()->createCommand()->delete(
            '{{%formierestapi_api_keys}}',
            ['like', 'name', self::ROW_PREFIX . '%', false],
        )->execute();
    }
}

final class CapturingApiKeysController extends ApiKeysController
{
    public function requireIndexPermission(): void
    {
        $this->requirePermission('formieRestApi:manageApiKeys');
    }

    public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): Response
    {
        $response = new Response();
        $response->data = ['template' => $template, 'variables' => $variables];

        return $response;
    }
}

final class ExplicitPermissionConsoleUser extends \craft\console\User
{
    /** @param list<string> $permissions */
    public function __construct(private readonly array $permissions)
    {
        parent::__construct();
    }

    public function checkPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->permissions, true);
    }

    public function getRemainingSessionTime(): int
    {
        return -1;
    }

    public function getImpersonator(): ?User
    {
        return null;
    }
}

final class ApiKeysTestApplication extends ConsoleApplication
{
    public function __construct(
        private readonly ConsoleApplication $application,
        private readonly RecordingSession $session,
    ) {
        $this->edition = $application->edition;
        $this->loadedModules = $application->loadedModules;
    }

    public function getSession(): RecordingSession
    {
        return $this->session;
    }

    public function get($id, $throwException = true): ?object
    {
        if ($id === 'session') {
            return $this->session;
        }

        return $this->application->get($id, $throwException);
    }

    public function has($id, $checkInstance = false): bool
    {
        return $id === 'session' || $this->application->has($id, $checkInstance);
    }

    public function getComponents($returnDefinitions = true): array
    {
        return $this->application->getComponents($returnDefinitions);
    }

    public function getModule($id, $load = true): ?\yii\base\Module
    {
        return $this->application->getModule($id, $load);
    }
}

final class RecordingSession extends Session
{
    /** @var array<string, mixed> */
    private array $flashes = [];

    public function setFlash($key, $value = true, $removeAfterAccess = true): void
    {
        $this->flashes[(string)$key] = $value;
    }

    public function getFlash($key, $defaultValue = null, $delete = false): mixed
    {
        $key = (string)$key;
        $value = $this->flashes[$key] ?? $defaultValue;
        if ($delete) {
            unset($this->flashes[$key]);
        }

        return $value;
    }

    public function hasFlash($key): bool
    {
        return array_key_exists((string)$key, $this->flashes);
    }

    public function setNotice(string $message, array $settings = []): void
    {
        $this->setFlash('notice', $message);
    }

    public function setError(string $message, array $settings = []): void
    {
        $this->setFlash('error', $message);
    }

    public function peek(string $key): mixed
    {
        return $this->flashes[$key] ?? null;
    }
}
