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
use craft\helpers\Json;
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
        $lockName = 'formie-rest-api:ratelimit:' . hash('sha256', $apiKey);
        $mutex = Craft::$app->getMutex();

        // Serialize the read/check/write so concurrent requests can't all pass
        // the limit check before any of them writes. Fail open if we can't
        // acquire the lock within 2s — consistent with the cache-error path.
        if (!$mutex->acquire($lockName, 2)) {
            Craft::warning('Rate-limit mutex acquire failed (failing open)', 'formie-rest-api');
            return true;
        }

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
        } finally {
            $mutex->release($lockName);
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
     * Validate that the current request's client IP is allowed by the resolved
     * key's whitelist. Empty whitelist = no restriction (returns true).
     *
     * **Caveat:** `Craft::$app->request->getUserIP()` returns the address of
     * whatever sent the request to PHP. Behind a CDN or reverse proxy you must
     * configure Craft's `trustedHosts` / proxy headers correctly, otherwise this
     * matches the proxy IP (single-address whitelist) rather than the real
     * client. See README → "IP whitelist".
     *
     * @param array<string, mixed> $apiKeyData
     * @since 3.4.0
     */
    public function validateIpWhitelist(array $apiKeyData): bool
    {
        $whitelist = $apiKeyData['ipWhitelist'] ?? [];
        if (!is_array($whitelist) || $whitelist === []) {
            return true;
        }

        $clientIp = Craft::$app->request->getUserIP();
        if (!is_string($clientIp) || $clientIp === '') {
            return false;
        }

        foreach ($whitelist as $entry) {
            if (is_string($entry) && $this->ipMatches($clientIp, $entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether `$ip` matches `$pattern`. Supports IPv4, IPv6, and CIDR
     * notation for either family. Address families must match (an IPv4 client
     * never matches an IPv6 CIDR).
     *
     * Returns false on any unparseable input — callers fail-closed.
     */
    private function ipMatches(string $ip, string $pattern): bool
    {
        if ($ip === $pattern) {
            return true;
        }

        // Single-IP entry that didn't match exactly above
        if (!str_contains($pattern, '/')) {
            return false;
        }

        [$subnet, $maskStr] = explode('/', $pattern, 2);

        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false) {
            return false;
        }

        // Address families must match (4 bytes for IPv4, 16 bytes for IPv6)
        if (strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maskBits = filter_var($maskStr, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => strlen($ipBin) * 8]]);
        if ($maskBits === false) {
            return false;
        }

        // Compare leading $maskBits bits — full bytes first, then remainder
        $fullBytes = intdiv($maskBits, 8);
        $remBits = $maskBits % 8;

        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
            return false;
        }

        if ($remBits === 0) {
            return true;
        }

        $maskByte = ~((1 << (8 - $remBits)) - 1) & 0xFF;
        return (ord($ipBin[$fullBytes]) & $maskByte) === (ord($subnetBin[$fullBytes]) & $maskByte);
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
        ];

        Craft::info(Json::encode($log), 'formie-rest-api');
    }
    
    /**
     * Maximum clock skew between client and server, in seconds. Requests with a
     * timestamp older than this (or that far in the future) are rejected.
     */
    private const SIGNATURE_TIMESTAMP_WINDOW = 300;

    /**
     * Validate request HMAC signature against the resolved API key data.
     *
     * Expects two request headers from the client:
     *  - `X-Timestamp`: unix epoch seconds
     *  - `X-Signature`: hex-encoded HMAC-SHA256 of `method\npath\ntimestamp\nbody`,
     *    keyed by the per-key signing secret (env var `FORMIE_API_SIGNING_SECRET[_*]`).
     *
     * Returns false on any of: missing headers, missing signing secret, expired
     * timestamp (> 5 min skew), or signature mismatch.
     *
     * @param array<string, mixed> $apiKeyData
     * @since 3.4.0
     */
    public function validateRequestSignature(array $apiKeyData): bool
    {
        $secret = $apiKeyData['signingSecret'] ?? null;
        if (!is_string($secret) || $secret === '') {
            return false;
        }

        $signature = Craft::$app->request->getHeaders()->get('X-Signature');
        $timestamp = Craft::$app->request->getHeaders()->get('X-Timestamp');
        if (!is_string($signature) || $signature === '' || !is_string($timestamp) || $timestamp === '') {
            return false;
        }

        // Reject expired or far-future timestamps to defeat replay.
        if (abs(time() - (int) $timestamp) > self::SIGNATURE_TIMESTAMP_WINDOW) {
            return false;
        }

        $signatureBase = implode("\n", [
            Craft::$app->request->getMethod(),
            Craft::$app->request->getUrl(),
            $timestamp,
            Craft::$app->request->getRawBody(),
        ]);

        return hash_equals(hash_hmac('sha256', $signatureBase, $secret), $signature);
    }
}
