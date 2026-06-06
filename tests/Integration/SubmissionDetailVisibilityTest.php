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
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use yii\web\NotFoundHttpException;

/**
 * Regression coverage for audit P6.1: the submission-detail endpoint
 * (`GET /api/v1/formie/submissions/{id}`) must honour the same
 * "completed, non-spam" contract as the list endpoint and never return a
 * spam or incomplete submission by direct ID.
 *
 * @since 3.6.0
 */
final class SubmissionDetailVisibilityTest extends TestCase
{
    public function testActionSubmissionDetailReturnsCompletedSubmission(): void
    {
        $form = $this->seedForm();
        $submission = $this->seedSubmission($form, false, false);

        $result = $this->authedController()->actionSubmissionDetail((int) $submission->id);

        self::assertSame((int) $submission->id, $result['data']['id']);
    }

    public function testActionSubmissionDetailRejectsSpamSubmission(): void
    {
        $form = $this->seedForm();
        $spam = $this->seedSubmission($form, true, false);

        $this->expectException(NotFoundHttpException::class);
        $this->authedController()->actionSubmissionDetail((int) $spam->id);
    }

    public function testActionSubmissionDetailRejectsIncompleteSubmission(): void
    {
        $form = $this->seedForm();
        $incomplete = $this->seedSubmission($form, false, true);

        $this->expectException(NotFoundHttpException::class);
        $this->authedController()->actionSubmissionDetail((int) $incomplete->id);
    }

    /**
     * Ground-truth probe: documents that Formie's SubmissionQuery already
     * defaults `isSpam`/`isIncomplete` to false, so even a bare `find()->id()`
     * excludes them. The endpoint's explicit filter is therefore defensive —
     * it pins the contract against a future upstream default change or a stray
     * `status(null)`.
     */
    public function testFormieSubmissionQueryExcludesSpamAndIncompleteByDefault(): void
    {
        $form = $this->seedForm();
        $spam = $this->seedSubmission($form, true, false);
        $incomplete = $this->seedSubmission($form, false, true);

        self::assertNull(Submission::find()->id((int) $spam->id)->one());
        self::assertNull(Submission::find()->id((int) $incomplete->id)->one());
    }

    /**
     * Build an ApiController with a resolved key granting `read_submissions`,
     * bypassing `beforeAction()` (auth is exercised in the SecurityService
     * tests). The `apiKeyData` slot is private, so it is set via reflection.
     */
    private function authedController(): ApiController
    {
        $controller = new ApiController('api', FormieRestApi::$plugin);

        $apiKeyData = new \ReflectionProperty(ApiController::class, 'apiKeyData');
        $apiKeyData->setValue($controller, ['permissions' => ['read_submissions']]);

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

    private function seedSubmission(Form $form, bool $isSpam, bool $isIncomplete): Submission
    {
        $submission = new Submission();
        $submission->setForm($form);
        $submission->title = $this->nextTestMarker('formieApiTest', 'submission');
        $submission->isSpam = $isSpam;
        $submission->isIncomplete = $isIncomplete;
        $this->saveTestElement($submission);

        return $submission;
    }
}
