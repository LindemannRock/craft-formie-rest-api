<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\services\SecurityService;
use lindemannrock\formierestapi\tests\TestCase;

/**
 * Pins the contract for {@see SecurityService::checkRateLimit()} and
 * {@see SecurityService::getRateLimitHeaders()}.
 *
 * Audit 1.4 wrapped the read/check/write in a mutex to close a TOCTOU race.
 * The window is 1 hour (fixed buckets), the cache backend is whatever
 * `Craft::$app->cache` resolves to in the test install, and the kill switch
 * is `FORMIE_API_RATE_LIMIT_DISABLED=1`.
 *
 * Each test uses a marker-prefixed API key so {@see TestCase::cleanupExternalState()}
 * can clear the rate-limit cache slot in tearDown.
 */
final class SecurityServiceRateLimitTest extends TestCase
{
    public function testIncrementsUpToBudgetThenRejects(): void
    {
        $apiKey = self::MARKER . 'ratelimit_' . uniqid('', true);
        $this->trackRateLimitKey($apiKey);
        $apiKeyData = ['rateLimit' => 3];

        // Make sure the kill switch isn't masking real behaviour.
        $this->setEnv('FORMIE_API_RATE_LIMIT_DISABLED', null);

        $service = new SecurityService();

        $this->assertTrue($service->checkRateLimit($apiKey, $apiKeyData), 'Call 1 / 3 — under budget.');
        $this->assertTrue($service->checkRateLimit($apiKey, $apiKeyData), 'Call 2 / 3 — under budget.');
        $this->assertTrue($service->checkRateLimit($apiKey, $apiKeyData), 'Call 3 / 3 — last allowed call.');
        $this->assertFalse(
            $service->checkRateLimit($apiKey, $apiKeyData),
            'Call 4 — budget exhausted, must reject.',
        );
        $this->assertFalse(
            $service->checkRateLimit($apiKey, $apiKeyData),
            'Repeated calls after budget exhaustion stay rejected.',
        );
    }

    public function testKillSwitchAllowsAllRegardlessOfCounter(): void
    {
        $apiKey = self::MARKER . 'killswitch_' . uniqid('', true);
        $this->trackRateLimitKey($apiKey);
        $apiKeyData = ['rateLimit' => 1];

        $this->setEnv('FORMIE_API_RATE_LIMIT_DISABLED', '1');

        $service = new SecurityService();

        // Way more than the budget — the kill switch must short-circuit.
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue(
                $service->checkRateLimit($apiKey, $apiKeyData),
                "FORMIE_API_RATE_LIMIT_DISABLED=1 must allow request {$i} despite rateLimit=1.",
            );
        }
    }

    public function testHeadersReportLimitRemainingAndReset(): void
    {
        $apiKey = self::MARKER . 'headers_' . uniqid('', true);
        $this->trackRateLimitKey($apiKey);
        $apiKeyData = ['rateLimit' => 5];

        $this->setEnv('FORMIE_API_RATE_LIMIT_DISABLED', null);
        $service = new SecurityService();

        $headersInitial = $service->getRateLimitHeaders($apiKey, $apiKeyData);
        $this->assertSame('5', $headersInitial['X-RateLimit-Limit']);
        $this->assertSame('5', $headersInitial['X-RateLimit-Remaining'], 'Fresh key starts with the full budget.');
        $reset = (int) $headersInitial['X-RateLimit-Reset'];
        $this->assertGreaterThan(time(), $reset, 'Reset must be in the future.');
        $this->assertLessThanOrEqual(time() + 3600, $reset, 'Reset is within the 1-hour bucket window.');

        // Consume two calls; remaining drops by two.
        $service->checkRateLimit($apiKey, $apiKeyData);
        $service->checkRateLimit($apiKey, $apiKeyData);

        $headersAfter = $service->getRateLimitHeaders($apiKey, $apiKeyData);
        $this->assertSame('5', $headersAfter['X-RateLimit-Limit']);
        $this->assertSame('3', $headersAfter['X-RateLimit-Remaining'], 'Two used → 3 remaining of 5.');
    }
}
