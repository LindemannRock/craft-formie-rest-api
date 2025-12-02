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
use craft\helpers\DateTimeHelper;

class SecurityService extends Component
{
    /**
     * Rate limiting tracking
     * In production, use Redis or database
     */
    private static array $rateLimitCache = [];
    
    /**
     * Check rate limit for API key
     *
     * @param string $apiKey
     * @param array $apiKeyData
     * @return bool
     */
    public function checkRateLimit(string $apiKey, array $apiKeyData): bool
    {
        $limit = $apiKeyData['rateLimit'] ?? 100;
        $window = 3600; // 1 hour window
        
        // In production, use Redis:
        // $redis = Craft::$app->redis;
        // $key = "rate_limit:{$apiKey}";
        // $current = $redis->incr($key);
        // if ($current === 1) {
        //     $redis->expire($key, $window);
        // }
        // return $current <= $limit;
        
        // Simple in-memory implementation for demo
        $now = time();
        $windowStart = $now - $window;
        
        if (!isset(self::$rateLimitCache[$apiKey])) {
            self::$rateLimitCache[$apiKey] = [];
        }
        
        // Clean old entries
        self::$rateLimitCache[$apiKey] = array_filter(
            self::$rateLimitCache[$apiKey],
            fn($timestamp) => $timestamp > $windowStart
        );
        
        // Check limit
        if (count(self::$rateLimitCache[$apiKey]) >= $limit) {
            return false;
        }
        
        // Add current request
        self::$rateLimitCache[$apiKey][] = $now;
        
        return true;
    }
    
    /**
     * Get rate limit headers
     *
     * @param string $apiKey
     * @param array $apiKeyData
     * @return array
     */
    public function getRateLimitHeaders(string $apiKey, array $apiKeyData): array
    {
        $limit = $apiKeyData['rateLimit'] ?? 100;
        $used = count(self::$rateLimitCache[$apiKey] ?? []);
        $remaining = max(0, $limit - $used);
        $reset = time() + 3600;
        
        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $reset,
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
            list($subnet, $mask) = explode('/', $pattern);
            $subnet = ip2long($subnet);
            $ip = ip2long($ip);
            $mask = -1 << (32 - $mask);
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
        
        $signatureBase = implode("\n", [$method, $path, $timestamp, $body]);
        $expectedSignature = hash_hmac('sha256', $signatureBase, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Get CORS headers based on environment
     *
     * @return array
     */
    public function getCorsHeaders(): array
    {
        $headers = [];
        
        // Configure allowed origins per environment
        $allowedOrigins = match (Craft::$app->env) {
            'production' => ['https://alhatab.com.sa', 'https://sap.alhatab.com'],
            'staging' => ['https://staging.alhatab.com.sa'],
            'dev' => ['http://localhost:3000', 'https://ahf.ddev.site'],
            default => ['*'],
        };
        
        $origin = Craft::$app->request->getHeaders()->get('Origin');
        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
            $headers['Access-Control-Allow-Origin'] = $origin ?: '*';
            $headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS';
            $headers['Access-Control-Allow-Headers'] = 'X-API-Key, Content-Type, X-Timestamp, X-Signature';
            $headers['Access-Control-Max-Age'] = '86400';
        }
        
        return $headers;
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
