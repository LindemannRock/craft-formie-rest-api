<?php
/**
 * API Key Service
 *
 * Centralized service for managing API key validation and permissions
 *
 * @author LindemannRock
 * @copyright Copyright (c) 2025 LindemannRock
 * @link https://lindemannrock.com
 * @package FormieRestApi
 * @since 1.0.0
 */

namespace lindemannrock\formierestapi\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;

class ApiKeyService extends Component
{
    /**
     * @var array<string, array<string, mixed>>|null Memoized key map for the
     * current request. Populated on first call to `getValidApiKeys()`.
     */
    private ?array $cachedKeys = null;

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
        
        $validKeys = $this->getValidApiKeys();
        
        return $validKeys[$apiKey] ?? false;
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
        $primaryKey = Craft::$app->config->general->formieApiKey ?? App::env('FORMIE_API_KEY');
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
        $secondaryKey = Craft::$app->config->general->formieApiKeyLimited ?? App::env('FORMIE_API_KEY_LIMITED');
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
        
        // Hook for adding custom keys (e.g., from database)
        $customKeys = $this->getCustomApiKeys();
        $keys = array_merge($keys, $customKeys);

        $this->cachedKeys = $keys;
        return $keys;
    }
    
    /**
     * Get custom API keys (e.g., from database)
     * Override this method to add database-stored keys
     *
     * @return array
     */
    protected function getCustomApiKeys(): array
    {
        // In production, this would query a database table
        // Example structure:
        /*
        $keys = [];
        $records = ApiKeyRecord::find()
            ->where(['enabled' => true])
            ->all();

        foreach ($records as $record) {
            $keys[$record->key] = [
                'name' => $record->name,
                'permissions' => json_decode($record->permissions, true),
                'rateLimit' => $record->rateLimit,
                'environment' => 'custom',
            ];
        }

        return $keys;
        */
        
        return [];
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
     * Read a signing-secret env var. Returns null if unset/empty.
     *
     * @since 3.4.0
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
     * @since 3.4.0
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
