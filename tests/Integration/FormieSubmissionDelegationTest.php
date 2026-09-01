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
use craft\console\User as ConsoleUser;
use lindemannrock\formierestapi\controllers\ApiKeysController;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\tests\TestCase;
use lindemannrock\formierestapi\traits\FormieSubmissionPermissionTrait;
use ReflectionMethod;
use verbb\formie\elements\Form;
use yii\web\ForbiddenHttpException;

/**
 * Verifies that CP users cannot delegate broader Formie submission access.
 *
 * @since 3.10.2
 */
final class FormieSubmissionDelegationTest extends TestCase
{
    public function testGlobalPermissionCanDelegateAllForms(): void
    {
        $firstForm = $this->seedForm();
        $secondForm = $this->seedForm();

        $this->withPermissions(['formie-viewSubmissions'], function() use ($firstForm, $secondForm): void {
            $acl = new FormieSubmissionDelegationHarness();

            self::assertSame(
                [$firstForm->handle, $secondForm->handle],
                $this->filteredHandles($acl, [$firstForm, $secondForm]),
            );

            $apiKey = new ApiKey();
            $apiKey->allowedForms = [ApiKey::ALL_FORMS];
            $acl->requireScope($apiKey);
            $this->addToAssertionCount(1);

            $apiKey->allowedForms = ['deletedFormHandle'];
            $acl->requireScope($apiKey);
            $this->addToAssertionCount(1);
        });
    }

    public function testPerFormPermissionOnlyExposesAndAcceptsMatchingForm(): void
    {
        $allowedForm = $this->seedForm();
        $deniedForm = $this->seedForm();

        $this->withPermissions(["formie-viewSubmissions:{$allowedForm->uid}"], function() use ($allowedForm, $deniedForm): void {
            $acl = new FormieSubmissionDelegationHarness();

            self::assertSame(
                [$allowedForm->handle],
                $this->filteredHandles($acl, [$allowedForm, $deniedForm]),
            );

            $apiKey = new ApiKey();
            $apiKey->allowedForms = [$allowedForm->handle];
            $acl->requireScope($apiKey);
            $this->addToAssertionCount(1);

            $deniedKey = new ApiKey();
            $deniedKey->allowedForms = [$deniedForm->handle];

            $wildcardKey = new ApiKey();
            $wildcardKey->allowedForms = [ApiKey::ALL_FORMS];

            $emptyKey = new ApiKey();
            $emptyKey->allowedForms = [];

            self::assertSame(
                [$apiKey, $emptyKey],
                $acl->filterKeys([$apiKey, $deniedKey, $wildcardKey, $emptyKey]),
            );
        });
    }

    public function testPerFormPermissionCannotDelegateAnotherForm(): void
    {
        $allowedForm = $this->seedForm();
        $deniedForm = $this->seedForm();

        $this->withPermissions(["formie-viewSubmissions:{$allowedForm->uid}"], function() use ($deniedForm): void {
            $apiKey = new ApiKey();
            $apiKey->allowedForms = [$deniedForm->handle];

            $this->expectException(ForbiddenHttpException::class);
            $this->expectExceptionMessage('User does not have permission to manage this API key because it includes forms outside their Formie submission access.');
            (new FormieSubmissionDelegationHarness())->requireScope($apiKey);
        });
    }

    public function testPerFormPermissionCannotDelegateWildcardScope(): void
    {
        $allowedForm = $this->seedForm();

        $this->withPermissions(["formie-viewSubmissions:{$allowedForm->uid}"], function(): void {
            $apiKey = new ApiKey();
            $apiKey->allowedForms = [ApiKey::ALL_FORMS];

            $this->expectException(ForbiddenHttpException::class);
            (new FormieSubmissionDelegationHarness())->requireScope($apiKey);
        });
    }

    public function testControllerAndTemplateKeepUiAndServerEnforcementTogether(): void
    {
        $indexBody = $this->methodSource(ApiKeysController::class, 'actionIndex');
        $aclFilter = strpos($indexBody, 'filterApiKeysByFormieSubmissionAccess($keys)');
        $statusFilter = strpos($indexBody, "if (\$statusFilter === 'enabled')");
        self::assertIsInt($aclFilter);
        self::assertIsInt($statusFilter);
        self::assertLessThan($statusFilter, $aclFilter);

        $editBody = $this->methodSource(ApiKeysController::class, 'actionEdit');
        self::assertStringContainsString('filterFormsByFormieSubmissionAccess($allForms)', $editBody);
        self::assertStringContainsString("'canAllowAllForms' => \$this->canViewAllFormieSubmissions()", $editBody);
        self::assertStringContainsString('requireApiKeyFormieSubmissionScope($apiKey)', $editBody);

        $saveBody = $this->methodSource(ApiKeysController::class, 'actionSave');
        $populate = strpos($saveBody, 'populateRestrictionsFromRequest($apiKey, $request)');
        $existingScope = strpos($saveBody, 'requireApiKeyFormieSubmissionScope($apiKey)');
        $requestedScope = strpos(
            $saveBody,
            'requireApiKeyFormieSubmissionScope($apiKey)',
            is_int($populate) ? $populate : 0,
        );
        $save = strpos($saveBody, 'if (!$apiKey->save())');
        self::assertIsInt($populate);
        self::assertIsInt($existingScope);
        self::assertIsInt($requestedScope);
        self::assertIsInt($save);
        self::assertLessThan($populate, $existingScope);
        self::assertLessThan($requestedScope, $populate);
        self::assertLessThan($save, $requestedScope);

        $deleteBody = $this->methodSource(ApiKeysController::class, 'actionDelete');
        $deleteScope = strpos($deleteBody, 'requireApiKeyFormieSubmissionScope($apiKey)');
        $delete = strpos($deleteBody, 'if (!$apiKey->delete())');
        self::assertIsInt($deleteScope);
        self::assertIsInt($delete);
        self::assertLessThan($delete, $deleteScope);

        $bulkDeleteBody = $this->methodSource(ApiKeysController::class, 'actionBulkDelete');
        $bulkDeleteScope = strpos($bulkDeleteBody, 'requireApiKeyFormieSubmissionScopes($ids)');
        $bulkDelete = strpos($bulkDeleteBody, 'bulkDelete($ids)');
        self::assertIsInt($bulkDeleteScope);
        self::assertIsInt($bulkDelete);
        self::assertLessThan($bulkDelete, $bulkDeleteScope);

        $bulkBody = $this->methodSource(ApiKeysController::class, 'runBulkSetEnabled');
        $bulkScope = strpos($bulkBody, 'requireApiKeyFormieSubmissionScopes($ids)');
        $bulkSetEnabled = strpos($bulkBody, 'bulkSetEnabled($ids, $enabled)');
        self::assertIsInt($bulkScope);
        self::assertIsInt($bulkSetEnabled);
        self::assertLessThan($bulkSetEnabled, $bulkScope);

        $template = file_get_contents(dirname(__DIR__, 2) . '/src/templates/api-keys/edit.twig');
        self::assertIsString($template);
        self::assertStringContainsString('{% if canAllowAllForms %}', $template);
    }

    /**
     * @param Form[] $forms
     * @return string[]
     */
    private function filteredHandles(FormieSubmissionDelegationHarness $acl, array $forms): array
    {
        return array_map(
            static fn(Form $form): string => (string)$form->handle,
            $acl->filter($forms),
        );
    }

    private function seedForm(): Form
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API Delegation ', 'form');
        $form->handle = $this->nextTestMarker('formieApiDelegation', 'form');
        $this->saveTestElement($form);

        return $form;
    }

    /**
     * @param string[] $permissions
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    private function withPermissions(array $permissions, callable $callback): mixed
    {
        $originalUser = Craft::$app->getUser();
        Craft::$app->set('user', new FormieSubmissionDelegationUser($permissions));

        try {
            return $callback();
        } finally {
            Craft::$app->set('user', $originalUser);
        }
    }

    /**
     * @param class-string $class
     */
    private function methodSource(string $class, string $method): string
    {
        $reflection = new ReflectionMethod($class, $method);
        $filename = $reflection->getFileName();
        self::assertIsString($filename);

        $lines = file($filename);
        self::assertIsArray($lines);

        return implode('', array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - $reflection->getStartLine() + 1,
        ));
    }
}

/**
 * Test harness exposing the trait's protected behavioral contract.
 *
 * @since 3.10.2
 */
final class FormieSubmissionDelegationHarness
{
    use FormieSubmissionPermissionTrait;

    /**
     * @param Form[] $forms
     * @return Form[]
     */
    public function filter(array $forms): array
    {
        return $this->filterFormsByFormieSubmissionAccess($forms);
    }

    public function requireScope(ApiKey $apiKey): void
    {
        $this->requireApiKeyFormieSubmissionScope($apiKey);
    }

    /**
     * @param ApiKey[] $apiKeys
     * @return ApiKey[]
     */
    public function filterKeys(array $apiKeys): array
    {
        return $this->filterApiKeysByFormieSubmissionAccess($apiKeys);
    }
}

/**
 * Craft user component with an explicit permission set.
 *
 * @since 3.10.2
 */
final class FormieSubmissionDelegationUser extends ConsoleUser
{
    /** @param string[] $permissions */
    public function __construct(private readonly array $permissions)
    {
        parent::__construct();
    }

    public function checkPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->permissions, true);
    }

    public function getId(): ?int
    {
        return 1;
    }

    public function getIsGuest(): bool
    {
        return false;
    }
}
