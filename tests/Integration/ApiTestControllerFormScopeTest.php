<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\controllers\ApiTestController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
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

    /**
     * ApiTestController resolved with a DB-key-shaped data array scoped to
     * $forms, bypassing beforeAction() (auth is exercised elsewhere).
     *
     * @param string[] $forms
     */
    private function scopedController(array $forms): ApiTestController
    {
        $controller = new ApiTestController('api-test', FormieRestApi::$plugin);

        $property = new \ReflectionProperty(ApiTestController::class, 'apiKeyData');
        $property->setValue($controller, [
            'permissions' => ['read_forms', 'read_submissions'],
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
}
