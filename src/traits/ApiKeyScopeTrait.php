<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\traits;

use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use yii\web\ForbiddenHttpException;

/**
 * Shared API-key permission + form-scoping checks for the public REST
 * controllers. Both the production controller and the devMode-only test
 * controller use these so the test endpoints faithfully preview the
 * production API's permission and form-scoping behaviour.
 *
 * @since 3.10.1
 */
trait ApiKeyScopeTrait
{
    /**
     * @var array<string, mixed>|null Resolved API key data for the current request.
     */
    protected ?array $apiKeyData = null;

    /**
     * Throw 403 unless the resolved key has the given permission scope.
     */
    protected function requireApiPermission(string $permission): void
    {
        if (!FormieRestApi::$plugin->apiKey->hasPermission($this->apiKeyData ?? [], $permission)) {
            throw new ForbiddenHttpException("API key does not have permission: {$permission}");
        }
    }

    /**
     * The resolved key's form-handle allowlist, or null when unrestricted
     * (a wildcard `*` key; or, defensively, a key with no allowlist set).
     *
     * @return string[]|null
     */
    protected function scopedFormHandles(): ?array
    {
        $allowed = $this->apiKeyData['allowedForms'] ?? null;
        if (!is_array($allowed) || in_array(ApiKey::ALL_FORMS, $allowed, true)) {
            return null;
        }

        return $allowed;
    }

    /**
     * Throw 403 when the resolved key is form-scoped and $formHandle is
     * outside its allowlist. No-op for unrestricted keys.
     */
    protected function requireFormInScope(string $formHandle): void
    {
        $allowed = $this->scopedFormHandles();
        if ($allowed !== null && !in_array($formHandle, $allowed, true)) {
            throw new ForbiddenHttpException('API key is not allowed to access this form');
        }
    }
}
