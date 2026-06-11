<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\controllers;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use lindemannrock\base\helpers\CpNavHelper;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use verbb\formie\elements\Form;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * API Keys Controller
 *
 * CP CRUD for DB-managed API keys.
 *
 * Permission model:
 *   manageApiKeys  — page access + view list/edit form (no mutations)
 *   createApiKeys  — POST a brand-new key
 *   editApiKeys    — POST changes to an existing key's metadata/restrictions
 *   revokeApiKeys  — DELETE a key
 *
 * Plaintext credentials are shown exactly once: after a successful create,
 * the key AND its paired signing secret are stashed in the session flash and
 * the operator is redirected to the edit page, which reveals both via
 * copy-to-clipboard banners. Edit of an existing key never reveals either
 * (the key exists only as a hash; the secret stays encrypted at rest).
 * Rotation = revoke + create.
 *
 * @since 3.10.0
 */
class ApiKeysController extends Controller
{
    use LoggingTrait;

    /**
     * Session flash keys for stashing the just-generated credentials between
     * the save redirect and the subsequent edit-page render.
     */
    private const FLASH_NEW_PLAINTEXT = 'fra.apiKey.newPlaintext';
    private const FLASH_NEW_SECRET = 'fra.apiKey.newSecret';

    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('formie-rest-api');
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function actionIndex(): Response
    {
        $user = Craft::$app->getUser();

        // This is also the plugin's CP nav default route — users without
        // API-keys access land on their first accessible section instead
        // of a 403.
        if (!$user->checkPermission('formieRestApi:manageApiKeys')) {
            $settings = FormieRestApi::$plugin->getSettings();
            $sections = FormieRestApi::$plugin->getCpSections($settings, true);
            $route = CpNavHelper::firstAccessibleRoute($user, $settings, $sections);
            if ($route) {
                return $this->redirect($route);
            }

            // No access at all - show 403
            $this->requirePermission('formieRestApi:manageApiKeys');
        }

        $request = Craft::$app->getRequest();

        // ---- Param parsing + allowlist validation -------------------------
        $statusFilter = (string)$request->getQueryParam('status', 'all');
        $validStatuses = ['all', 'enabled', 'disabled'];
        if (!in_array($statusFilter, $validStatuses, true)) {
            $statusFilter = 'all';
        }

        // 64-char cap on user input as a defensive clamp against runaway payloads.
        $search = trim((string)$request->getQueryParam('search', ''));
        if (mb_strlen($search) > 64) {
            $search = mb_substr($search, 0, 64);
        }

        $validSortFields = ['name', 'status', 'allowedForms', 'validUntil', 'lastUsedAt'];
        $sort = (string)$request->getParam('sort', 'name');
        if (!in_array($sort, $validSortFields, true)) {
            $sort = 'name';
        }
        $dir = strtolower((string)$request->getParam('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // ---- Load + filter ------------------------------------------------
        $keys = ApiKey::findAll();

        // Cached before filter narrows the collection so the beforeTable
        // "no API keys yet" info-box renders correctly regardless of the
        // current filter state.
        $hasAnyKeys = !empty($keys);

        if ($statusFilter === 'enabled') {
            $keys = array_values(array_filter($keys, fn(ApiKey $k): bool => $k->enabled));
        } elseif ($statusFilter === 'disabled') {
            $keys = array_values(array_filter($keys, fn(ApiKey $k): bool => !$k->enabled));
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $keys = array_values(array_filter($keys, function(ApiKey $k) use ($needle): bool {
                return str_contains(mb_strtolower($k->name), $needle)
                    || str_contains(mb_strtolower($k->keyPrefix), $needle);
            }));
        }

        // ---- Sort + paginate ----------------------------------------------
        $keys = $this->sortKeys($keys, $sort, $dir);

        // Total count is computed after filtering so the pager reflects the
        // visible subset, not the underlying table.
        $totalCount = count($keys);
        $page = max(1, (int)$request->getParam('page', 1));
        $limit = 100;
        $offset = ($page - 1) * $limit;
        $keys = array_slice($keys, $offset, $limit);

        return $this->renderTemplate('formie-rest-api/api-keys/index', [
            'keys' => $keys,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'hasAnyKeys' => $hasAnyKeys,
            'page' => $page,
            'limit' => $limit,
            'totalCount' => $totalCount,
            'canCreate' => $user->checkPermission('formieRestApi:createApiKeys'),
            'canEdit' => $user->checkPermission('formieRestApi:editApiKeys'),
            'canRevoke' => $user->checkPermission('formieRestApi:revokeApiKeys'),
        ]);
    }

    // =========================================================================
    // EDIT (new + existing share this action)
    // =========================================================================

    public function actionEdit(?int $keyId = null, ?ApiKey $apiKey = null): Response
    {
        $this->requirePermission('formieRestApi:manageApiKeys');

        $isNew = ($keyId === null);

        // When a save action re-renders due to validation errors it passes an
        // already-populated $apiKey through; in all other cases load or build.
        if ($apiKey === null) {
            if ($isNew) {
                $apiKey = new ApiKey();
            } else {
                $apiKey = ApiKey::findById($keyId);
                if ($apiKey === null) {
                    throw new NotFoundHttpException(Craft::t('formie-rest-api', 'API key not found'));
                }
            }
        }

        $title = $isNew
            ? Craft::t('formie-rest-api', 'New API Key')
            : Craft::t('formie-rest-api', 'Edit API Key');

        // Pull the credentials stashed during a fresh create (one-shot reveal).
        // Craft's session->getFlash() consumes the value on read.
        $newPlaintext = Craft::$app->getSession()->getFlash(self::FLASH_NEW_PLAINTEXT);
        $newSecret = Craft::$app->getSession()->getFlash(self::FLASH_NEW_SECRET);

        return $this->renderTemplate('formie-rest-api/api-keys/edit', [
            'apiKey' => $apiKey,
            'isNew' => $isNew,
            'title' => $title,
            'allForms' => Form::find()->orderBy('title')->all(),
            'newPlaintext' => is_string($newPlaintext) ? $newPlaintext : null,
            'newSecret' => is_string($newSecret) ? $newSecret : null,
            'canCreate' => Craft::$app->getUser()->checkPermission('formieRestApi:createApiKeys'),
            'canEdit' => Craft::$app->getUser()->checkPermission('formieRestApi:editApiKeys'),
            'canRevoke' => Craft::$app->getUser()->checkPermission('formieRestApi:revokeApiKeys'),
        ]);
    }

    // =========================================================================
    // SAVE
    // =========================================================================

    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $keyId = $request->getBodyParam('keyId') !== null
            ? (int)$request->getBodyParam('keyId')
            : null;
        $isNew = ($keyId === null);

        $this->requirePermission($isNew ? 'formieRestApi:createApiKeys' : 'formieRestApi:editApiKeys');

        if ($isNew) {
            $apiKey = new ApiKey();

            // Generate key + paired signing secret once. Both plaintexts go to
            // session flash for the one-time reveal on redirect; only the key
            // hash and the encrypted secret are persisted.
            $service = FormieRestApi::$plugin->apiKey;
            $generated = $service->generateDbKey();
            $secret = $service->generateSigningSecret();
            $apiKey->keyHash = $generated['hash'];
            $apiKey->keyPrefix = $generated['prefix'];
            $apiKey->signingSecretEnc = $service->encryptSigningSecret($secret);
        } else {
            $apiKey = ApiKey::findById($keyId);
            if ($apiKey === null) {
                throw new NotFoundHttpException(Craft::t('formie-rest-api', 'API key not found'));
            }
            // keyHash / keyPrefix / signingSecretEnc are locked once generated —
            // rotation = revoke + create.
        }

        $this->populateRestrictionsFromRequest($apiKey, $request);

        if (!$apiKey->save()) {
            Craft::$app->getSession()->setError(Craft::t('formie-rest-api', 'Couldn’t save API key'));
            // Re-render the form with the unsaved model so errors surface
            // beside their fields. Craft's runAction routes to the same view.
            Craft::$app->getUrlManager()->setRouteParams([
                'apiKey' => $apiKey,
                'keyId' => $keyId,
            ]);
            return null;
        }

        if ($isNew) {
            Craft::$app->getSession()->setFlash(self::FLASH_NEW_PLAINTEXT, $generated['plaintext']);
            Craft::$app->getSession()->setFlash(self::FLASH_NEW_SECRET, $secret);
            Craft::$app->getSession()->setNotice(Craft::t('formie-rest-api', 'API key created'));
        } else {
            Craft::$app->getSession()->setNotice(Craft::t('formie-rest-api', 'API key saved'));
        }

        // New keys always land back on the edit page so the one-time
        // credential reveal banners have somewhere to render. Existing keys
        // honour the posted redirect.
        if ($isNew) {
            return $this->redirect('formie-rest-api/api-keys/edit/' . $apiKey->id);
        }

        return $this->redirectToPostedUrl($apiKey);
    }

    // =========================================================================
    // DELETE / REVOKE
    // =========================================================================

    public function actionDelete(?int $keyId = null): ?Response
    {
        $this->requirePostRequest();
        $this->requirePermission('formieRestApi:revokeApiKeys');

        $request = Craft::$app->getRequest();
        $acceptsJson = $request->getAcceptsJson();

        $keyId ??= (int)$request->getBodyParam('keyId');
        if (!$keyId) {
            throw new NotFoundHttpException(Craft::t('formie-rest-api', 'API key not found'));
        }

        $apiKey = ApiKey::findById($keyId);
        if ($apiKey === null) {
            throw new NotFoundHttpException(Craft::t('formie-rest-api', 'API key not found'));
        }

        if (!$apiKey->delete()) {
            $errorMessage = Craft::t('formie-rest-api', 'Couldn’t revoke API key');
            if ($acceptsJson) {
                return $this->asJson(['success' => false, 'error' => $errorMessage]);
            }
            Craft::$app->getSession()->setError($errorMessage);
            return $this->redirect('formie-rest-api/api-keys');
        }

        $successMessage = Craft::t('formie-rest-api', 'API key revoked');
        if ($acceptsJson) {
            // Caller (e.g. row-action JS using Craft.sendActionRequest) handles
            // its own reload; returning a redirect would force the AJAX client
            // to render the index server-side just to throw it away.
            return $this->asJson(['success' => true, 'message' => $successMessage]);
        }
        Craft::$app->getSession()->setNotice($successMessage);
        return $this->redirect('formie-rest-api/api-keys');
    }

    // =========================================================================
    // BULK ACTIONS
    // =========================================================================

    public function actionBulkEnable(): ?Response
    {
        return $this->runBulkSetEnabled(true);
    }

    public function actionBulkDisable(): ?Response
    {
        return $this->runBulkSetEnabled(false);
    }

    public function actionBulkDelete(): ?Response
    {
        $this->requirePostRequest();
        $this->requirePermission('formieRestApi:revokeApiKeys');

        $ids = $this->parseBulkIds(Craft::$app->getRequest()->getBodyParam('ids', []));
        $deleted = FormieRestApi::$plugin->apiKey->bulkDelete($ids);

        return $this->respondToBulkResult(
            $deleted,
            Craft::t('formie-rest-api', '{count, plural, =1{1 API key revoked} other{# API keys revoked}}', ['count' => $deleted]),
            Craft::t('formie-rest-api', 'Couldn’t revoke API keys'),
        );
    }

    private function runBulkSetEnabled(bool $enabled): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('formieRestApi:editApiKeys');

        $ids = $this->parseBulkIds(Craft::$app->getRequest()->getBodyParam('ids', []));
        $affected = FormieRestApi::$plugin->apiKey->bulkSetEnabled($ids, $enabled);

        $message = $enabled
            ? Craft::t('formie-rest-api', '{count, plural, =1{1 API key enabled} other{# API keys enabled}}', ['count' => $affected])
            : Craft::t('formie-rest-api', '{count, plural, =1{1 API key disabled} other{# API keys disabled}}', ['count' => $affected]);

        return $this->respondToBulkResult(
            $affected,
            $message,
            $enabled
                ? Craft::t('formie-rest-api', 'Couldn’t enable API keys')
                : Craft::t('formie-rest-api', 'Couldn’t disable API keys'),
        );
    }

    /**
     * @param array<mixed>|mixed $raw
     * @return int[]
     */
    private function parseBulkIds(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $ids = [];
        foreach ($raw as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                $ids[] = (int) $value;
            }
        }
        return array_values(array_unique($ids));
    }

    private function respondToBulkResult(int $count, string $successMessage, string $emptyMessage): Response
    {
        $acceptsJson = Craft::$app->getRequest()->getAcceptsJson();

        if ($count > 0) {
            if ($acceptsJson) {
                return $this->asJson(['success' => true, 'count' => $count, 'message' => $successMessage]);
            }
            Craft::$app->getSession()->setNotice($successMessage);
            return $this->redirect('formie-rest-api/api-keys');
        }

        if ($acceptsJson) {
            return $this->asJson(['success' => false, 'count' => 0, 'error' => $emptyMessage]);
        }
        Craft::$app->getSession()->setError($emptyMessage);
        return $this->redirect('formie-rest-api/api-keys');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Pull restriction fields from POST body into the model. Centralised so
     * create and edit normalize the same way.
     */
    private function populateRestrictionsFromRequest(ApiKey $apiKey, \craft\web\Request $request): void
    {
        $apiKey->name = trim((string)$request->getBodyParam('name', ''));
        $apiKey->enabled = (bool)$request->getBodyParam('enabled', true);
        $apiKey->requireSignature = (bool)$request->getBodyParam('requireSignature', true);
        $apiKey->canReadSubmissions = (bool)$request->getBodyParam('canReadSubmissions', true);

        // Forms: "All forms" toggle (allowAllForms=1) → ['*']. Otherwise an
        // array of explicit handles. The form makes these mutually exclusive
        // on the client; this is the server-side enforcement.
        if ((bool)$request->getBodyParam('allowAllForms', false)) {
            $apiKey->allowedForms = [ApiKey::ALL_FORMS];
        } else {
            $rawForms = $request->getBodyParam('allowedForms', []);
            $apiKey->allowedForms = is_array($rawForms)
                ? array_values(array_filter(array_map('strval', $rawForms), fn($h) => $h !== ''))
                : [];
        }

        // IP whitelist: textarea → array. Trim, lowercase (IPv6 hex), drop
        // blanks. Entry shape is checked by the model's validation rule.
        $rawIps = (string)$request->getBodyParam('ipWhitelist', '');
        $ips = [];
        foreach (preg_split('/\r\n|\r|\n/', $rawIps) ?: [] as $line) {
            $trimmed = strtolower(trim($line));
            if ($trimmed !== '') {
                $ips[] = $trimmed;
            }
        }
        $apiKey->ipWhitelist = array_values(array_unique($ips));

        // Optional numeric field — empty input means null (default limit).
        $apiKey->rateLimit = $this->parseOptionalInt($request->getBodyParam('rateLimit'));

        // Optional expiry — Craft's datetime picker submits an array {date, time}
        // or a single string. Use Craft's helper for consistent parsing.
        $apiKey->validUntil = DateTimeHelper::toDateTime($request->getBodyParam('validUntil')) ?: null;
    }

    private function parseOptionalInt(mixed $raw): ?int
    {
        if ($raw === null || $raw === '' || (is_string($raw) && trim($raw) === '')) {
            return null;
        }
        if (!is_numeric($raw)) {
            return null;
        }
        return (int)$raw;
    }

    /**
     * Sort the loaded keys array in PHP. Small dataset → array-side sort is fine.
     *
     * @param ApiKey[] $keys
     * @return ApiKey[]
     */
    private function sortKeys(array $keys, string $sort, string $dir): array
    {
        $multiplier = $dir === 'desc' ? -1 : 1;

        usort($keys, function(ApiKey $a, ApiKey $b) use ($sort, $multiplier): int {
            $cmp = match ($sort) {
                'status' => strcmp($a->getStatus(), $b->getStatus()),
                'allowedForms' => count($a->allowedForms) <=> count($b->allowedForms),
                'validUntil' => $this->compareNullableDates($a->validUntil, $b->validUntil),
                'lastUsedAt' => $this->compareNullableDates($a->lastUsedAt, $b->lastUsedAt),
                default => strcasecmp($a->name, $b->name),
            };

            // Stable tie-break by name so equal primary keys don't shuffle
            // between requests — keeps pagination predictable.
            if ($cmp === 0 && $sort !== 'name') {
                $cmp = strcasecmp($a->name, $b->name);
            }

            return $cmp * $multiplier;
        });

        return $keys;
    }

    /**
     * Null-aware datetime comparison. Null sorts AFTER non-null in ascending
     * order ("Never" / "—" feels like a high value at the bottom), keeping
     * keys with real dates surfaced first.
     */
    private function compareNullableDates(?\DateTime $a, ?\DateTime $b): int
    {
        if ($a === null && $b === null) {
            return 0;
        }
        if ($a === null) {
            return 1;
        }
        if ($b === null) {
            return -1;
        }
        return $a <=> $b;
    }
}
