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
use lindemannrock\formierestapi\controllers\ApiTestController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use yii\web\BadRequestHttpException;

/**
 * List endpoints accept only bounded integer pagination while preserving their
 * established defaults and response metadata.
 *
 * @since 3.10.2
 */
final class ApiPaginationContractTest extends TestCase
{
    public function testProductionDefaultsAndBoundariesAppearInMetadata(): void
    {
        $controller = $this->productionController();

        $this->installRequestStub(new StubApiRequest());
        $formsDefault = $controller->actionForms();
        $submissionsDefault = $controller->actionSubmissions();
        self::assertSame(100, $formsDefault['meta']['limit']);
        self::assertSame(0, $formsDefault['meta']['offset']);
        self::assertSame(100, $submissionsDefault['meta']['limit']);
        self::assertSame(0, $submissionsDefault['meta']['offset']);

        $this->installRequestStub(new StubApiRequest(apiParams: ['limit' => '1', 'offset' => '0']));
        $minimum = $controller->actionForms();
        self::assertSame(1, $minimum['meta']['limit']);
        self::assertSame(0, $minimum['meta']['offset']);

        $this->installRequestStub(new StubApiRequest(apiParams: ['limit' => '100', 'offset' => '7']));
        $maximum = $controller->actionSubmissions();
        self::assertSame(100, $maximum['meta']['limit']);
        self::assertSame(7, $maximum['meta']['offset']);
    }

    public function testProductionRejectsMalformedAndOutOfRangePagination(): void
    {
        foreach (['', 'abc', '1.5', '0', '-1', '101', '999999999999999999999999999999', []] as $invalidLimit) {
            $this->installRequestStub(new StubApiRequest(apiParams: ['limit' => $invalidLimit]));
            $this->assertBadRequest(fn(): array => $this->productionController()->actionForms());

            $this->installRequestStub(new StubApiRequest(apiParams: ['limit' => $invalidLimit]));
            $this->assertBadRequest(fn(): array => $this->productionController()->actionSubmissions());
        }

        foreach (['', 'abc', '1.5', '-1', '999999999999999999999999999999', []] as $invalidOffset) {
            $this->installRequestStub(new StubApiRequest(apiParams: ['offset' => $invalidOffset]));
            $this->assertBadRequest(fn(): array => $this->productionController()->actionForms());

            $this->installRequestStub(new StubApiRequest(apiParams: ['offset' => $invalidOffset]));
            $this->assertBadRequest(fn(): array => $this->productionController()->actionSubmissions());
        }
    }

    public function testDevPaginationPreservesDefaultsAndAcceptsBoundaries(): void
    {
        $form = $this->seedForm();
        $this->installWebResponse();
        $controller = $this->devController();
        $this->installRequestStub(new StubApiRequest(apiParams: ['formHandle' => $form->handle]));
        $default = $controller->actionSubmissions();
        self::assertIsArray($default->data);
        self::assertSame(10, $default->data['data']['pagination']['perPage']);
        self::assertSame(1, $default->data['data']['pagination']['currentPage']);

        $this->installRequestStub(new StubApiRequest(apiParams: [
            'formHandle' => $form->handle,
            'limit' => '100',
            'page' => '2',
        ]));
        $boundary = $controller->actionSubmissions();
        self::assertIsArray($boundary->data);
        self::assertSame(100, $boundary->data['data']['pagination']['perPage']);
        self::assertSame(2, $boundary->data['data']['pagination']['currentPage']);
    }

    public function testDevRejectsMalformedAndOutOfRangePagination(): void
    {
        foreach (['', 'abc', '1.5', '0', '-1', '101', '999999999999999999999999999999', []] as $invalidLimit) {
            $this->installRequestStub(new StubApiRequest(apiParams: ['limit' => $invalidLimit]));
            $this->assertBadRequest(fn(): \yii\web\Response => $this->devController()->actionSubmissions());
        }

        foreach (['', 'abc', '1.5', '0', '-1', '999999999999999999999999999999', []] as $invalidPage) {
            $this->installRequestStub(new StubApiRequest(apiParams: ['page' => $invalidPage]));
            $this->assertBadRequest(fn(): \yii\web\Response => $this->devController()->actionSubmissions());
        }
    }

    private function productionController(): ApiController
    {
        $controller = new ApiController('api', FormieRestApi::$plugin);
        $this->setApiKeyData($controller);
        return $controller;
    }

    private function devController(): ApiTestController
    {
        $controller = new ApiTestController('api-test', FormieRestApi::$plugin);
        $this->setApiKeyData($controller);
        return $controller;
    }

    private function setApiKeyData(ApiController|ApiTestController $controller): void
    {
        $property = new \ReflectionProperty($controller, 'apiKeyData');
        $property->setValue($controller, [
            'permissions' => ['read_forms', 'read_submissions'],
            'allowedForms' => [ApiKey::ALL_FORMS],
        ]);
    }

    private function assertBadRequest(callable $action): void
    {
        try {
            $action();
            self::fail('Invalid pagination must return HTTP 400.');
        } catch (BadRequestHttpException $e) {
            self::assertSame(400, $e->statusCode);
        }
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
