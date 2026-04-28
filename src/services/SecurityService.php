<?php
/**
 * Security Service
 *
 * Comprehensive security features for API protection
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
use craft\helpers\DateTimeHelper;
use lindemannrock\base\helpers\PluginHelper;

class SecurityService extends Component
{
    /**
     * Length of the rate-limit window in seconds.
     */
    private const RATE_LIMIT_WINDOW = 3600;

    /**
     * Build the cache key for the current rate-limit window.
     */
    private function rateLimitCacheKey(string $apiKey): string
    {
        $bucket = (int) (time() / self::RATE_LIMIT_WINDOW);
        return PluginHelper::getCacheKeyPrefix('formie-rest-api', 'ratelimit')
            . hash('sha256', $apiKey)
            . ':' . $bucket;
    }

    /**
     * Check whether the request is within the rate-limit budget. Increments the
     * counter on success. Fails open (returns true) if the kill-switch env var
     * FORMIE_API_RATE_LIMIT_DISABLED=1 is set or if the cache backend errors.
     *
     * @since 3.4.0
     */
    public function checkRateLimit(string $apiKey, array $apiKeyData): bool
    {
        if ((bool) App::env('FORMIE_API_RATE_LIMIT_DISABLED')) {
            return true;
        }

        $limit = (int) ($apiKeyData['rateLimit'] ?? 100);
        $cacheKey = $this->rateLimitCacheKey($apiKey);

        try {
            $current = (int) Craft::$app->cache->get($cacheKey);

            if ($current >= $limit) {
                return false;
            }

            Craft::$app->cache->set($cacheKey, $current + 1, self::RATE_LIMIT_WINDOW);
            return true;
        } catch (\Throwable $e) {
            Craft::warning('Rate-limit cache error (failing open): ' . $e->getMessage(), 'formie-rest-api');
            return true;
        }
    }

    /**
     * Get rate-limit response headers for the current window.
     *
     * @since 3.4.0
     */
    public function getRateLimitHeaders(string $apiKey, array $apiKeyData): array
    {
        $limit = (int) ($apiKeyData['rateLimit'] ?? 100);

        try {
            $used = (int) Craft::$app->cache->get($this->rateLimitCacheKey($apiKey));
        } catch (\Throwable) {
            $used = 0;
        }

        $remaining = max(0, $limit - $used);
        $reset = (((int) (time() / self::RATE_LIMIT_WINDOW)) + 1) * self::RATE_LIMIT_WINDOW;

        return [
            'X-RateLimit-Limit' => (string) $limit,
            'X-RateLimit-Remaining' => (string) $remaining,
            'X-RateLimit-Reset' => (string) $reset,
        ];
    }
    
    /**
     * Validate IP whitelist
     *
     * @param array $whitelist
     * @return bool
     */
    public function validateIpWhitelist(array $whitelist): bool
    {
        if (empty($whitelist)) {
            return true; // No whitelist means all IPs allowed
        }
        
        $clientIp = Craft::$app->request->getUserIP();
        
        foreach ($whitelist as $allowedIp) {
            if ($this->ipMatches($clientIp, $allowedIp)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP matches pattern (supports CIDR)
     *
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatches(string $ip, string $pattern): bool
    {
        if ($ip === $pattern) {
            return true;
        }
        
        // CIDR notation support
        if (strpos($pattern, '/') !== false) {
            list($subnet, $maskStr) = explode('/', $pattern);
            $subnet = ip2long($subnet);
            $ip = ip2long($ip);
            $mask = -1 << (32 - (int)$maskStr);
            $subnet &= $mask;
            return ($ip & $mask) == $subnet;
        }
        
        return false;
    }
    
    /**
     * Log API access
     *
     * @param string $apiKey
     * @param string $endpoint
     * @param array $params
     * @param int $responseCode
     */
    public function logApiAccess(string $apiKey, string $endpoint, array $params, int $responseCode): void
    {
        // Remove sensitive data
        unset($params['password'], $params['token'], $params['secret']);
        
        $log = [
            'timestamp' => DateTimeHelper::currentTimeStamp(),
            'api_key' => substr($apiKey, 0, 10) . '...', // Only log partial key
            'endpoint' => $endpoint,
            'method' => Craft::$app->request->getMethod(),
            'ip' => Craft::$app->request->getUserIP(),
            'user_agent' => Craft::$app->request->getUserAgent(),
            'params' => $params,
            'response_code' => $responseCode,
            'environment' => Craft::$app->env,
        ];
        
        // In production, send to logging service
        Craft::info(json_encode($log), 'formie-rest-api');
    }
    
    /**
     * Validate request signature (HMAC)
     *
     * @param string $apiKey
     * @param string $secret
     * @return bool
     */
    public function validateRequestSignature(string $apiKey, string $secret): bool
    {
        $signature = Craft::$app->request->getHeaders()->get('X-Signature');
        if (!$signature) {
            return false;
        }
        
        // Build signature base
        $method = Craft::$app->request->getMethod();
        $path = Craft::$app->request->getUrl();
        $timestamp = Craft::$app->request->getHeaders()->get('X-Timestamp');
        $body = Craft::$app->request->getRawBody();

        // Check timestamp to prevent replay attacks
        if (!$timestamp || abs(time() - (int)$timestamp) > 300) { // 5 minute window
            return false;
        }

        $signatureBase = implode("\n", [(string)$method, (string)$path, (string)$timestamp, (string)$body]);
        $expectedSignature = hash_hmac('sha256', $signatureBase, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Sanitize output to prevent data leakage
     *
     * @param array $data
     * @param array $allowedFields
     * @return array
     */
    public function sanitizeOutput(array $data, array $allowedFields): array
    {
        // Remove any fields not explicitly allowed
        return array_intersect_key($data, array_flip($allowedFields));
    }
}
