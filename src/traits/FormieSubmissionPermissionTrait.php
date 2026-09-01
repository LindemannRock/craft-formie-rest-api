<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\traits;

use Craft;
use lindemannrock\formierestapi\models\ApiKey;
use verbb\formie\elements\Form;
use yii\web\ForbiddenHttpException;

/**
 * Prevents CP users from delegating more Formie submission access than they hold.
 *
 * @since 3.10.2
 */
trait FormieSubmissionPermissionTrait
{
    /**
     * Whether the current user can view submissions for every Formie form.
     */
    protected function canViewAllFormieSubmissions(): bool
    {
        return Craft::$app->getUser()->checkPermission('formie-viewSubmissions');
    }

    /**
     * Whether the current user can view submissions for the given form.
     */
    protected function canViewFormieSubmissions(Form $form): bool
    {
        return $this->canViewAllFormieSubmissions()
            || Craft::$app->getUser()->checkPermission("formie-viewSubmissions:{$form->uid}");
    }

    /**
     * Remove forms the current user cannot access through Formie's submission ACL.
     *
     * @param Form[] $forms
     * @return Form[]
     */
    protected function filterFormsByFormieSubmissionAccess(array $forms): array
    {
        return array_values(array_filter(
            $forms,
            fn(Form $form): bool => $this->canViewFormieSubmissions($form),
        ));
    }

    /**
     * Remove API keys whose form scope exceeds the current user's Formie ACL.
     *
     * @param ApiKey[] $apiKeys
     * @return ApiKey[]
     */
    protected function filterApiKeysByFormieSubmissionAccess(array $apiKeys): array
    {
        if ($this->canViewAllFormieSubmissions()) {
            return array_values($apiKeys);
        }

        /** @var Form[] $forms */
        $forms = Form::find()->all();
        $permittedHandles = array_map(
            static fn(Form $form): string => (string)$form->handle,
            $this->filterFormsByFormieSubmissionAccess($forms),
        );

        return array_values(array_filter(
            $apiKeys,
            fn(ApiKey $apiKey): bool => $this->apiKeyScopeIsWithinHandles($apiKey, $permittedHandles),
        ));
    }

    /**
     * Require the current user to hold every Formie permission delegated by a key.
     *
     * Empty scopes are left to the model's existing enabled-key validation.
     *
     * @throws ForbiddenHttpException
     */
    protected function requireApiKeyFormieSubmissionScope(ApiKey $apiKey): void
    {
        // Global access also permits stale handles left behind by a deleted
        // form, so an administrator can still open and clean up the key.
        if ($this->canViewAllFormieSubmissions()) {
            return;
        }

        /** @var Form[] $forms */
        $forms = Form::find()->all();
        $permittedHandles = array_map(
            static fn(Form $form): string => (string)$form->handle,
            $this->filterFormsByFormieSubmissionAccess($forms),
        );

        if (!$this->apiKeyScopeIsWithinHandles($apiKey, $permittedHandles)) {
            throw new ForbiddenHttpException(Craft::t(
                'formie-rest-api',
                'User does not have permission to manage this API key because it includes forms outside their Formie submission access.',
            ));
        }
    }

    /**
     * Whether a key delegates only the supplied explicit form handles.
     *
     * Empty scopes are left to the model's enabled-key validation.
     *
     * @param string[] $permittedHandles
     */
    private function apiKeyScopeIsWithinHandles(ApiKey $apiKey, array $permittedHandles): bool
    {
        if ($apiKey->allowsAllForms()) {
            return false;
        }

        $requestedHandles = array_values(array_unique($apiKey->allowedForms));

        return $requestedHandles === []
            || array_diff($requestedHandles, $permittedHandles) === [];
    }
}
