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

class ApiKeyService extends Component
{
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
        $keys = [];
        
        // Primary API key with full access
        $primaryKey = Craft::$app->config->general->formieApiKey ?? getenv('FORMIE_API_KEY');
        if ($primaryKey) {
            $keys[$primaryKey] = [
                'name' => 'Primary API Key',
                'permissions' => ['read_forms', 'read_submissions', 'create_submissions'],
                'rateLimit' => $this->getRateLimitForEnvironment('primary'),
                'environment' => Craft::$app->env,
                'ipWhitelist' => $this->getIpWhitelistForEnvironment(),
                'requireSignature' => Craft::$app->env === 'production',
            ];
        }
        
        // Secondary API key with limited access
        $secondaryKey = Craft::$app->config->general->formieApiKeyLimited ?? getenv('FORMIE_API_KEY_LIMITED');
        if ($secondaryKey) {
            $keys[$secondaryKey] = [
                'name' => 'Limited Access Key',
                'permissions' => ['read_forms'],
                'rateLimit' => $this->getRateLimitForEnvironment('limited'),
                'environment' => Craft::$app->env,
                'ipWhitelist' => $this->getIpWhitelistForEnvironment(),
                'requireSignature' => Craft::$app->env === 'production',
            ];
        }
        
        // Test key for development (only in dev mode)
        if (Craft::$app->config->general->devMode) {
            $testKey = getenv('FORMIE_API_KEY_TEST') ?: 'test_key_dev_only';
            $keys[$testKey] = [
                'name' => 'Development Test Key',
                'permissions' => ['read_forms', 'read_submissions', 'create_submissions'],
                'rateLimit' => 1000,
                'environment' => 'development',
            ];
        }
        
        // Hook for adding custom keys (e.g., from database)
        $customKeys = $this->getCustomApiKeys();
        $keys = array_merge($keys, $customKeys);
        
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
    public function generateApiKey(string $prefix = 'sk_'): string
    {
        return $prefix . bin2hex(random_bytes(32));
    }
    
    /**
     * Get rate limit based on environment and key type
     * 
     * @param string $keyType
     * @return int
     */
    private function getRateLimitForEnvironment(string $keyType): int
    {
        return match([Craft::$app->env, $keyType]) {
            ['production', 'primary'] => 1000,
            ['production', 'limited'] => 100,
            ['staging', 'primary'] => 500,
            ['staging', 'limited'] => 50,
            default => 1000, // Development
        };
    }
    
    /**
     * Get IP whitelist for environment
     * 
     * @return array
     */
    private function getIpWhitelistForEnvironment(): array
    {
        // In production, you might want to restrict to specific IPs
        // return match(Craft::$app->env) {
        //     'production' => ['192.168.1.0/24', '10.0.0.0/8'],
        //     'staging' => ['192.168.1.0/24'],
        //     default => [], // No restrictions in dev
        // };
        
        return []; // No IP restrictions for now
    }
}