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
    public function testConcurrentAttemptsCannotCollectivelyExceedBudget(): void
    {
        $apiKey = self::MARKER . 'concurrent_' . bin2hex(random_bytes(8));
        $this->trackRateLimitKey($apiKey);
        $this->setEnv('FORMIE_API_RATE_LIMIT_DISABLED', null);
        $limit = 2;
        $attempts = 6;
        $sharedCachePath = sys_get_temp_dir()
            . '/lindemannrock-base-phpunit-cache.'
            . bin2hex(random_bytes(8));
        \craft\helpers\FileHelper::createDirectory($sharedCachePath);
        $this->trackTempPath($sharedCachePath);
        $this->setEnv('LINDEMANNROCK_BASE_TEST_CACHE_PATH', $sharedCachePath);
        $tempDirectory = $this->createTrackedTempDirectory('__formieapi_rate_limit_');
        $socketPath = $tempDirectory . '/barrier.sock';
        $socketUri = 'unix://' . $socketPath;
        $errorCode = 0;
        $errorMessage = '';
        $server = stream_socket_server($socketUri, $errorCode, $errorMessage);
        self::assertIsResource($server, "Barrier socket must open: {$errorCode} {$errorMessage}");

        /** @var list<resource> $connections */
        $connections = [];
        /** @var list<array{process: resource, pipes: array<int, resource>}> $workers */
        $workers = [];

        try {
            $workerPath = dirname(__DIR__) . '/Support/RateLimitAttemptWorker.php';
            for ($i = 0; $i < $attempts; $i++) {
                $pipes = [];
                $process = proc_open(
                    [PHP_BINARY, $workerPath, $socketUri, $apiKey, (string) $limit],
                    [
                        0 => ['file', '/dev/null', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ],
                    $pipes,
                    dirname(__DIR__, 2),
                );
                self::assertIsResource($process, "Worker {$i} must start.");
                $workers[] = ['process' => $process, 'pipes' => $pipes];
            }

            for ($i = 0; $i < $attempts; $i++) {
                $connection = stream_socket_accept($server, 15);
                self::assertIsResource($connection, "Worker {$i} must reach the barrier.");
                stream_set_timeout($connection, 15);
                self::assertSame("READY\n", fgets($connection), "Worker {$i} must report ready.");
                $connections[] = $connection;
            }

            foreach ($connections as $connection) {
                self::assertSame(3, fwrite($connection, "GO\n"));
            }

            $allowed = 0;
            foreach ($connections as $i => $connection) {
                $result = fgets($connection);
                self::assertContains($result, ["ALLOWED\n", "REJECTED\n"], "Worker {$i} must return a result.");
                $allowed += $result === "ALLOWED\n" ? 1 : 0;
            }

            foreach ($connections as $connection) {
                self::assertSame(6, fwrite($connection, "CLOSE\n"));
            }

            foreach ($workers as $i => $worker) {
                $stdout = stream_get_contents($worker['pipes'][1]);
                $stderr = stream_get_contents($worker['pipes'][2]);
                fclose($worker['pipes'][1]);
                fclose($worker['pipes'][2]);
                $exitCode = proc_close($worker['process']);
                self::assertSame(0, $exitCode, "Worker {$i} failed. stdout={$stdout} stderr={$stderr}");
            }
            $workers = [];

            self::assertSame($limit, $allowed, 'Simultaneous attempts cannot collectively exceed the shared budget.');
        } finally {
            foreach ($connections as $connection) {
                if (is_resource($connection)) {
                    fclose($connection);
                }
            }
            if (is_resource($server)) {
                fclose($server);
            }
            foreach ($workers as $worker) {
                foreach ($worker['pipes'] as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }
                $status = proc_get_status($worker['process']);
                if ($status['running']) {
                    proc_terminate($worker['process']);
                }
                proc_close($worker['process']);
            }
            if (file_exists($socketPath)) {
                unlink($socketPath);
            }
        }
    }

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
