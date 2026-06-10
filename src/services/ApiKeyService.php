<?php
/**
 * API Key Service
 *
 * Centralized service for managing API key validation and permissions
 *
 * @author LindemannRock
 * @copyright Copyright (c) 2025-2026 LindemannRock
 * @link https://lindemannrock.com
 * @package FormieRestApi
 * @since 1.0.0
 */

namespace lindemannrock\formierestapi\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\Db;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\logginglibrary\traits\LoggingTrait;

class ApiKeyService extends Component
{
    use LoggingTrait;

    /**
     * Plaintext key prefix shared by env-var and DB-managed keys.
     *
     * @since 3.10.0
     */
    public const KEY_PREFIX = 'fra_';

    /**
     * Length of the stored/displayed prefix: `fra_` + 8 hex chars.
     *
     * @since 3.10.0
     */
    public const PREFIX_LENGTH = 12;

    /**
     * @var array<string, array<string, mixed>>|null Memoized key map for the
     * current request. Populated on first call to `getValidApiKeys()`.
     */
    private ?array $cachedKeys = null;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('formie-rest-api');
    }

    /**
     * Validate API key and return key data if valid
     *
     * @param string|null $apiKey
     * @return array|false
     */
    public function validateApiKey(?string $apiKey): array|false
    {
        if (!$apiKey) {
            return false;
        }

        // Env-var keys first (plaintext-keyed map, unchanged legacy path)
        $validKeys = $this->getValidApiKeys();
        if (isset($validKeys[$apiKey])) {
            return $validKeys[$apiKey];
        }

        // CP-managed DB keys: prefix lookup + constant-time hash verify.
        // Unknown, disabled, and expired all fail undifferentiated — the
        // caller returns a generic 401 with no leaked detail.
        $dbKey = $this->findByPlaintextKey($apiKey);
        if ($dbKey === null || !$dbKey->isStillValid()) {
            return false;
        }

        return $this->toApiKeyData($dbKey);
    }
    
    /**
     * Check if API key has required permission
     *
     * @param array $apiKeyData
     * @param string $permission
     * @return bool
     */
    public function hasPermission(array $apiKeyData, string $permission): bool
    {
        return in_array($permission, $apiKeyData['permissions'] ?? []);
    }
    
    /**
     * Get valid API keys from environment variables or database
     *
     * @return array
     */
    public function getValidApiKeys(): array
    {
        if ($this->cachedKeys !== null) {
            return $this->cachedKeys;
        }

        $keys = [];
        
        // Primary API key with full access
        $primaryKey = App::env('FORMIE_API_KEY');
        if ($primaryKey) {
            $primarySecret = $this->resolveSigningSecret('FORMIE_API_SIGNING_SECRET');
            $keys[$primaryKey] = [
                'name' => 'Primary API Key',
                'permissions' => ['read_forms', 'read_submissions', 'create_submissions'],
                'rateLimit' => $this->getRateLimitForEnvironment('primary'),
                'environment' => Craft::$app->env,
                'ipWhitelist' => $this->resolveIpWhitelist('FORMIE_API_IP_WHITELIST'),
                'signingSecret' => $primarySecret,
                'requireSignature' => $primarySecret !== null,
            ];
        }

        // Secondary API key with limited access
        $secondaryKey = App::env('FORMIE_API_KEY_LIMITED');
        if ($secondaryKey) {
            $limitedSecret = $this->resolveSigningSecret('FORMIE_API_SIGNING_SECRET_LIMITED');
            $keys[$secondaryKey] = [
                'name' => 'Limited Access Key',
                'permissions' => ['read_forms'],
                'rateLimit' => $this->getRateLimitForEnvironment('limited'),
                'environment' => Craft::$app->env,
                'ipWhitelist' => $this->resolveIpWhitelist('FORMIE_API_IP_WHITELIST_LIMITED'),
                'signingSecret' => $limitedSecret,
                'requireSignature' => $limitedSecret !== null,
            ];
        }

        // Test key for development (only in dev mode, only if explicitly set in env)
        if (Craft::$app->config->general->devMode) {
            $testKey = App::env('FORMIE_API_KEY_TEST');
            if (is_string($testKey) && $testKey !== '') {
                $testSecret = $this->resolveSigningSecret('FORMIE_API_SIGNING_SECRET_TEST');
                $keys[$testKey] = [
                    'name' => 'Development Test Key',
                    'permissions' => ['read_forms', 'read_submissions', 'create_submissions'],
                    'rateLimit' => 1000,
                    'environment' => 'development',
                    'ipWhitelist' => $this->resolveIpWhitelist('FORMIE_API_IP_WHITELIST_TEST'),
                    'signingSecret' => $testSecret,
                    'requireSignature' => $testSecret !== null,
                ];
            }
        }
        
        $this->cachedKeys = $keys;
        return $keys;
    }

    /**
     * Generate a secure API key
     *
     * @param string $prefix
     * @return string
     */
    public function generateApiKey(string $prefix = 'fra_'): string
    {
        return $prefix . bin2hex(random_bytes(32));
    }

    /**
     * Generate a secure HMAC signing secret (no prefix, 64 hex chars).
     *
     * @since 3.4.0
     */
    public function generateSigningSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate a fresh plaintext key for a CP-managed (DB) key, plus its
     * derived prefix and hash. The plaintext is the caller's only chance to
     * capture the full value — only the prefix + hash are persisted.
     *
     * @return array{plaintext: string, prefix: string, hash: string}
     * @since 3.10.0
     */
    public function generateDbKey(): array
    {
        $plaintext = $this->generateApiKey(self::KEY_PREFIX);

        return [
            'plaintext' => $plaintext,
            'prefix' => substr($plaintext, 0, self::PREFIX_LENGTH),
            'hash' => $this->hashKey($plaintext),
        ];
    }

    /**
     * Compute the HMAC-SHA256 hash for a plaintext key.
     * Keyed by Craft's `securityKey` so hashes are not portable across installs
     * (defence in depth against a leaked DB dump replayed on another install).
     *
     * @since 3.10.0
     */
    public function hashKey(string $plaintext): string
    {
        $securityKey = Craft::$app->getConfig()->getGeneral()->securityKey;
        return hash_hmac('sha256', $plaintext, $securityKey);
    }

    /**
     * Constant-time check that $plaintext is the original of $key's stored hash.
     *
     * @since 3.10.0
     */
    public function verifyKey(string $plaintext, ApiKey $key): bool
    {
        // Prefix mismatch is a cheap pre-check — also catches typos before crypto.
        if (!str_starts_with($plaintext, $key->keyPrefix)) {
            return false;
        }
        return hash_equals($key->keyHash, $this->hashKey($plaintext));
    }

    /**
     * Enforcement hot-path lookup for DB-managed keys. Parses a presented
     * plaintext key, looks up the matching ApiKey row by prefix, and verifies
     * the hash.
     *
     * Returns the matching ApiKey on success, or null on any failure
     * (unknown prefix, hash mismatch, malformed input). Failure is
     * deliberately undifferentiated to the caller — the endpoint guard wraps
     * this in a generic 401 with no leaked detail about why.
     *
     * @since 3.10.0
     */
    public function findByPlaintextKey(string $plaintext): ?ApiKey
    {
        if (strlen($plaintext) < self::PREFIX_LENGTH) {
            return null;
        }

        $prefix = substr($plaintext, 0, self::PREFIX_LENGTH);
        $key = ApiKey::findByPrefix($prefix);
        if ($key === null) {
            return null;
        }

        return $this->verifyKey($plaintext, $key) ? $key : null;
    }

    /**
     * Encrypt an HMAC signing secret for at-rest storage. Unlike the key
     * (one-way hashed), the secret must be recoverable — the server recomputes
     * request signatures with it. Keyed by Craft's `securityKey`.
     *
     * @since 3.10.0
     */
    public function encryptSigningSecret(string $secret): string
    {
        $securityKey = Craft::$app->getConfig()->getGeneral()->securityKey;
        return base64_encode(Craft::$app->getSecurity()->encryptByKey($secret, $securityKey));
    }

    /**
     * Decrypt a stored signing secret. Returns null on missing, corrupt, or
     * tampered ciphertext — the key then fails its signature check closed.
     *
     * @since 3.10.0
     */
    public function decryptSigningSecret(?string $encrypted): ?string
    {
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        $raw = base64_decode($encrypted, true);
        if ($raw === false) {
            return null;
        }

        try {
            $securityKey = Craft::$app->getConfig()->getGeneral()->securityKey;
            $secret = Craft::$app->getSecurity()->decryptByKey($raw, $securityKey);
        } catch (\Throwable) {
            return null;
        }

        return is_string($secret) && $secret !== '' ? $secret : null;
    }

    /**
     * Hydrate a DB key into the same `$apiKeyData` array shape the env-var
     * keys produce, so SecurityService (signature, IP whitelist, rate limit)
     * and the controller permission checks work unchanged for both kinds.
     *
     * Extra keys vs env entries: `allowedForms` (form-handle scoping; absent
     * for env keys = unrestricted) and `dbKey` (the model, for usage tracking).
     *
     * @return array<string, mixed>
     * @since 3.10.0
     */
    public function toApiKeyData(ApiKey $key): array
    {
        $permissions = ['read_forms'];
        if ($key->canReadSubmissions) {
            $permissions[] = 'read_submissions';
        }

        $secret = $this->decryptSigningSecret($key->signingSecretEnc);

        return [
            'name' => $key->name,
            'permissions' => $permissions,
            'rateLimit' => $key->rateLimit ?? 100,
            'environment' => Craft::$app->env,
            'ipWhitelist' => $key->ipWhitelist,
            'signingSecret' => $secret,
            'requireSignature' => $key->requireSignature && $secret !== null,
            'allowedForms' => $key->allowedForms,
            'dbKey' => $key,
        ];
    }

    /**
     * Update a key's last-used timestamp. Wrapped so a write failure can only
     * cost us the timestamp, never reject a legitimate request.
     *
     * @since 3.10.0
     */
    public function recordUsage(ApiKey $key): void
    {
        try {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            Craft::$app->getDb()->createCommand()
                ->update(
                    '{{%formierestapi_api_keys}}',
                    ['lastUsedAt' => Db::prepareDateForDb($now)],
                    ['id' => $key->id],
                )
                ->execute();
            $key->lastUsedAt = $now;
        } catch (\Throwable $e) {
            $this->logWarning('Failed to record API key usage timestamp', [
                'keyId' => $key->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enable or disable a set of keys by id in one UPDATE.
     * Returns the number of affected rows.
     *
     * @param array<mixed> $ids
     * @since 3.10.0
     */
    public function bulkSetEnabled(array $ids, bool $enabled): int
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return 0;
        }

        $affected = (int) Craft::$app->getDb()->createCommand()
            ->update(
                '{{%formierestapi_api_keys}}',
                [
                    'enabled' => (int) $enabled,
                    'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                ],
                ['id' => $ids],
            )
            ->execute();

        $this->logInfo('Bulk set API key enabled state', [
            'enabled' => $enabled,
            'requestedIds' => $ids,
            'affected' => $affected,
        ]);

        return $affected;
    }

    /**
     * Hard-delete a set of keys by id. Destructive — there is no recovery.
     * Returns the number of deleted rows.
     *
     * @param array<mixed> $ids
     * @since 3.10.0
     */
    public function bulkDelete(array $ids): int
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return 0;
        }

        $deleted = (int) Craft::$app->getDb()->createCommand()
            ->delete('{{%formierestapi_api_keys}}', ['id' => $ids])
            ->execute();

        $this->logInfo('Bulk revoke API keys', [
            'requestedIds' => $ids,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Filter, coerce, and dedupe an incoming list of ids. Drops anything
     * non-integer or non-positive so a malformed POST payload can't widen
     * the affected set or coerce a SQL surprise.
     *
     * @param array<mixed> $ids
     * @return int[]
     */
    private function normalizeIds(array $ids): array
    {
        $clean = [];
        foreach ($ids as $id) {
            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                $id = (int) $id;
                if ($id > 0) {
                    $clean[$id] = $id;
                }
            }
        }

        return array_values($clean);
    }

    /**
     * Read a signing-secret env var. Returns null if unset/empty.
     */
    private function resolveSigningSecret(string $envVar): ?string
    {
        $value = App::env($envVar);
        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Parse an IP-whitelist env var into a list of CIDR/IP entries. Empty/unset
     * env returns an empty array (= no restriction). Whitespace-trimmed,
     * empty entries dropped.
     *
     * Example value: `"203.0.113.5,192.168.1.0/24,2001:db8::/32"`
     *
     * @return array<int, string>
     */
    private function resolveIpWhitelist(string $envVar): array
    {
        $value = App::env($envVar);
        if (!is_string($value) || $value === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn(string $e) => $e !== ''));
    }
    
    /**
     * Get rate limit based on environment and key type
     *
     * @param string $keyType
     * @return int
     */
    private function getRateLimitForEnvironment(string $keyType): int
    {
        return match ([Craft::$app->env, $keyType]) {
            ['production', 'primary'] => 1000,
            ['production', 'limited'] => 100,
            ['staging', 'primary'] => 500,
            ['staging', 'limited'] => 50,
            default => 1000, // Development
        };
    }
}
