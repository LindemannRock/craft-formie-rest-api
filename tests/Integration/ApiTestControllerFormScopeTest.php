<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use craft\db\Query;
use craft\web\Response;
use lindemannrock\formierestapi\controllers\ApiTestController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use yii\web\ForbiddenHttpException;

/**
 * The devMode-only test endpoints must enforce the SAME per-key form scoping
 * as the production controller, so their output is a faithful preview rather
 * than leaking every form/submission to a form-scoped key. These drive the
 * query-param actions through a request stub and assert the scope guards fire.
 *
 * @since 3.10.1
 */
final class ApiTestControllerFormScopeTest extends TestCase
{
    public function testFormsRefusesOutOfScopeHandle(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();

        $controller = $this->scopedController([$allowed->handle]);
        $this->installRequestStub(new StubApiRequest(apiParams: ['handle' => $other->handle]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionForms();
    }

    public function testFormsScopeCheckPrecedesExistenceCheck(): void
    {
        // An out-of-scope key gets the same 403 whether or not the handle
        // exists — probing for form handles via the test endpoint leaks nothing.
        $controller = $this->scopedController(['someAllowedForm']);
        $this->installRequestStub(new StubApiRequest(apiParams: ['handle' => 'definitelyDoesNotExist']));

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionForms();
    }

    public function testSubmissionsRefusesOutOfScopeHandle(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();

        $controller = $this->scopedController([$allowed->handle]);
        $this->installRequestStub(new StubApiRequest(apiParams: ['formHandle' => $other->handle]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionSubmissions();
    }

    public function testNumericFormIdsDoNotDiscloseExistence(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();
        $controller = $this->scopedController([$allowed->handle]);

        $this->installRequestStub(new StubApiRequest(apiParams: ['id' => (int) $other->id]));
        $this->assertForbidden(fn() => $controller->actionForms());

        $this->installRequestStub(new StubApiRequest(apiParams: ['id' => $this->missingElementId()]));
        $this->assertForbidden(fn() => $controller->actionForms());
    }

    public function testNumericSubmissionFormIdsDoNotDiscloseExistence(): void
    {
        $allowed = $this->seedForm();
        $other = $this->seedForm();
        $controller = $this->scopedController([$allowed->handle]);

        $this->installRequestStub(new StubApiRequest(apiParams: ['formId' => (int) $other->id]));
        $this->assertForbidden(fn() => $controller->actionSubmissions());

        $this->installRequestStub(new StubApiRequest(apiParams: ['formId' => $this->missingElementId()]));
        $this->assertForbidden(fn() => $controller->actionSubmissions());
    }

    public function testFormsOnlyKeyDoesNotReceiveSubmissionCounts(): void
    {
        $form = $this->seedForm();
        $controller = $this->scopedController([$form->handle], ['read_forms']);
        $controller->response = new Response();
        $this->installRequestStub(new StubApiRequest(apiParams: ['handle' => $form->handle]));

        $response = $controller->actionForms();

        self::assertIsArray($response->data);
        self::assertArrayNotHasKey('submissionCount', $response->data['data']['forms'][0]);
    }

    public function testSubmissionReadingKeyReceivesSubmissionCounts(): void
    {
        $form = $this->seedForm();
        $this->seedSubmission($form);
        $controller = $this->scopedController([$form->handle]);
        $controller->response = new Response();
        $this->installRequestStub(new StubApiRequest(apiParams: ['handle' => $form->handle]));

        $response = $controller->actionForms();

        self::assertIsArray($response->data);
        self::assertSame(1, $response->data['data']['forms'][0]['submissionCount']);
    }

    /**
     * ApiTestController resolved with a DB-key-shaped data array scoped to
     * $forms, bypassing beforeAction() (auth is exercised elsewhere).
     *
     * @param string[] $forms
     */
    private function scopedController(
        array $forms,
        array $permissions = ['read_forms', 'read_submissions'],
    ): ApiTestController {
        $controller = new ApiTestController('api-test', FormieRestApi::$plugin);

        $property = new \ReflectionProperty(ApiTestController::class, 'apiKeyData');
        $property->setValue($controller, [
            'permissions' => $permissions,
            'allowedForms' => $forms,
        ]);

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

    private function assertForbidden(callable $action): void
    {
        try {
            $action();
            self::fail('Scoped numeric lookups must not disclose whether an object exists.');
        } catch (ForbiddenHttpException $e) {
            self::assertSame(403, $e->statusCode);
        }
    }

    private function missingElementId(): int
    {
        return (int) (new Query())->from('{{%elements}}')->max('id') + 1000;
    }
}
