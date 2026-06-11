<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\controllers\ApiController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Per-key form scoping (`allowedForms`): scoped keys can only read their
 * allowed forms and submissions; wildcard DB keys and env keys (which carry
 * no `allowedForms` entry) are unrestricted.
 *
 * Controller coverage exercises the console-safe actions (handle/id args).
 * The query-param paths of `actionSubmissions` share the same
 * `requireFormInScope()` / `scopedFormHandles()` guards asserted here.
 *
 * @since 3.10.0
 */
final class DbApiKeyFormScopeTest extends TestCase
{
    public function testWildcardKeyReadsAnyForm(): void
    {
        $form = $this->seedForm();

        $result = $this->scopedController([ApiKey::ALL_FORMS])->actionFormByHandle($form->handle);

        self::assertSame($form->handle, $result['data']['handle']);
    }

    public function testEnvStyleKeyWithoutAllowedFormsIsUnrestricted(): void
    {
        $form = $this->seedForm();

        // Env keys hydrate WITHOUT an allowedForms entry at all.
        $controller = $this->controllerWithKeyData(['permissions' => ['read_forms', 'read_submissions']]);
        $result = $controller->actionFormByHandle($form->handle);

        self::assertSame($form->handle, $result['data']['handle']);
    }

    public function testScopedKeyReadsAllowedFormAndIsRefusedOthers(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();

        $controller = $this->scopedController([$allowed->handle]);

        $result = $controller->actionFormByHandle($allowed->handle);
        self::assertSame($allowed->handle, $result['data']['handle']);

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionFormByHandle($other->handle);
    }

    public function testScopeCheckPrecedesExistenceCheck(): void
    {
        // An out-of-scope key gets the same 403 whether or not the handle
        // exists — probing for form handles leaks nothing.
        $controller = $this->scopedController(['someAllowedForm']);

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionFormByHandle('definitelyDoesNotExist');
    }

    public function testWildcardKeyStillGetsNotFoundForMissingForm(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->scopedController([ApiKey::ALL_FORMS])->actionFormByHandle('definitelyDoesNotExist');
    }

    public function testFormDetailByIdHonoursScope(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();

        $controller = $this->scopedController([$allowed->handle]);

        $result = $controller->actionFormDetail((int) $allowed->id);
        self::assertSame((int) $allowed->id, $result['data']['id']);

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionFormDetail((int) $other->id);
    }

    public function testSubmissionDetailHonoursScope(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();
        $allowedSubmission = $this->seedSubmission($allowed);
        $otherSubmission = $this->seedSubmission($other);

        $controller = $this->scopedController([$allowed->handle]);

        $result = $controller->actionSubmissionDetail((int) $allowedSubmission->id);
        self::assertSame((int) $allowedSubmission->id, $result['data']['id']);

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionSubmissionDetail((int) $otherSubmission->id);
    }

    public function testFormsOnlyKeyCannotReadSubmissions(): void
    {
        $form = $this->seedForm();
        $submission = $this->seedSubmission($form);

        // canReadSubmissions=false hydrates to permissions without read_submissions.
        $controller = $this->controllerWithKeyData([
            'permissions' => ['read_forms'],
            'allowedForms' => [ApiKey::ALL_FORMS],
        ]);

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionSubmissionDetail((int) $submission->id);
    }

    /**
     * Controller resolved with a DB-key-shaped data array scoped to $forms.
     *
     * @param string[] $forms
     */
    private function scopedController(array $forms): ApiController
    {
        return $this->controllerWithKeyData([
            'permissions' => ['read_forms', 'read_submissions'],
            'allowedForms' => $forms,
        ]);
    }

    /**
     * Build an ApiController with the given resolved key data, bypassing
     * `beforeAction()` (auth itself is exercised in DbApiKeyAuthenticationTest).
     *
     * @param array<string, mixed> $apiKeyData
     */
    private function controllerWithKeyData(array $apiKeyData): ApiController
    {
        $controller = new ApiController('api', FormieRestApi::$plugin);

        $property = new \ReflectionProperty(ApiController::class, 'apiKeyData');
        $property->setValue($controller, $apiKeyData);

        return $controller;
    }

    private function seedForm(): Form
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API Test ', 'form');
        $form->handle = $this->nextTestMarker('formieApiTest', 'form');
        $this->saveTestElement($form);

        return $form;
    }

    private function seedSubmission(Form $form): Submission
    {
        $submission = new Submission();
        $submission->setForm($form);
        $submission->title = $this->nextTestMarker('formieApiTest', 'submission');
        $this->saveTestElement($submission);

        return $submission;
    }
}
