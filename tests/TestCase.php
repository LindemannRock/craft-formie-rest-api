<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests;

use Craft;
use craft\helpers\App;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use yii\web\Request as YiiWebRequest;

/**
 * Base test case for formie-rest-api integration tests.
 *
 * Adds three plugin-specific shorthands on top of the shared
 * {@see IntegrationTestCase}:
 *
 *  - {@see setEnv()} / env auto-restore: tests resolve API key data straight
 *    out of env vars, so almost every test mutates one. Saved values from
 *    `$_SERVER` / `$_ENV` / `getenv()` are restored in tearDown.
 *  - {@see installRequestStub()} / request auto-restore: HMAC + IP-whitelist
 *    tests need a request that knows about headers / method / URL / raw body /
 *    client IP. The stub is installed on `Craft::$app->set('request', ...)`
 *    and the original component is restored in tearDown.
 *  - Rate-limit cache cleanup: any API key the test touched gets its rate-
 *    limit cache key purged in {@see cleanupExternalState()} so subsequent
 *    tests start with a fresh counter.
 *
 * Subclasses overriding `setUp()` or `tearDown()` MUST call parent at the
 * right edge (parent::tearDown() must run LAST so request + env restore +
 * external-state cleanup all fire correctly).
 */
abstract class TestCase extends IntegrationTestCase
{
    /**
     * Marker prefix used for every test-seeded API key string. Picked so a
     * stray key can never collide with a real production / dev key.
     */
    protected const MARKER = '__formieapi_test_';

    /**
     * Env vars touched during the current test, with their original values
     * (or null if previously unset). Restored in tearDown so cross-test env
     * leakage stays impossible.
     *
     * @var array<string, ?string>
     */
    private array $savedEnv = [];

    /**
     * Snapshot of Craft's original request component, captured the first time
     * {@see installRequestStub()} runs in a given test. Null until then.
     */
    private ?object $savedRequest = null;

    /**
     * API keys whose rate-limit cache the test exercised. Cleared at teardown
     * so the next test starts at zero.
     *
     * @var list<string>
     */
    private array $touchedRateLimitKeys = [];

    protected function tearDown(): void
    {
        $this->restoreSavedEnv();
        $this->restoreSavedRequest();
        // parent::tearDown() fires cleanupExternalState() before component
        // restoration — env + request restore above is independent of that.
        parent::tearDown();
    }

    /**
     * Override hook: clear the rate-limit cache keys this test populated so
     * the counter resets cleanly between tests. The bucket is `time() / 3600`,
     * so we cover the current bucket plus the immediately-adjacent ones in
     * case the clock crossed a boundary mid-test.
     */
    protected function cleanupExternalState(): void
    {
        $prefix = PluginHelper::getCacheKeyPrefix('formie-rest-api', 'ratelimit');
        $bucket = (int) (time() / 3600);

        foreach ($this->touchedRateLimitKeys as $apiKey) {
            $hash = hash('sha256', $apiKey);
            foreach ([-1, 0, 1] as $offset) {
                Craft::$app->cache->delete($prefix . $hash . ':' . ($bucket + $offset));
            }
        }

        $this->touchedRateLimitKeys = [];
    }

    /**
     * Set an env var so {@see App::env()} returns $value on the next read.
     * Passing null clears the variable. Original value is captured on first
     * touch and restored in tearDown.
     *
     * Writes all three of `$_SERVER`, `$_ENV`, and `putenv()` because
     * `App::env()` consults `$_SERVER` first, then `$_ENV`, then `getenv()`
     * — covering all three keeps the env consistent regardless of which path
     * the helper takes.
     */
    protected function setEnv(string $name, ?string $value): void
    {
        if (!array_key_exists($name, $this->savedEnv)) {
            $raw = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);
            $this->savedEnv[$name] = ($raw === false || $raw === null) ? null : (string) $raw;
        }

        if ($value === null) {
            unset($_SERVER[$name], $_ENV[$name]);
            putenv($name);
            return;
        }

        $_SERVER[$name] = $value;
        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }

    /**
     * Install a request stub on `Craft::$app->set('request', …)`. The first
     * call within a test snapshots the original component so tearDown can
     * restore it. Subsequent calls in the same test just swap the stub —
     * the original snapshot is preserved.
     */
    protected function installRequestStub(StubApiRequest $stub): void
    {
        if ($this->savedRequest === null) {
            $this->savedRequest = Craft::$app->getRequest();
        }
        Craft::$app->set('request', $stub);
    }

    /**
     * Record an API key whose rate-limit cache this test populated, so
     * {@see cleanupExternalState()} can drop it before the next test.
     */
    protected function trackRateLimitKey(string $apiKey): void
    {
        $this->touchedRateLimitKeys[] = $apiKey;
    }

    /**
     * Build the canonical HMAC signature for the given inputs. Matches the
     * server-side calculation in
     * {@see \lindemannrock\formierestapi\services\SecurityService::validateRequestSignature()}.
     */
    protected function buildSignature(string $secret, string $method, string $url, string $timestamp, string $body): string
    {
        $base = implode("\n", [$method, $url, $timestamp, $body]);
        return hash_hmac('sha256', $base, $secret);
    }

    private function restoreSavedEnv(): void
    {
        foreach ($this->savedEnv as $name => $original) {
            if ($original === null) {
                unset($_SERVER[$name], $_ENV[$name]);
                putenv($name);
                continue;
            }
            $_SERVER[$name] = $original;
            $_ENV[$name] = $original;
            putenv("{$name}={$original}");
        }
        $this->savedEnv = [];
    }

    private function restoreSavedRequest(): void
    {
        if ($this->savedRequest === null) {
            return;
        }
        // We installed a stub at some point; restore whatever the app had
        // before (typically the console Request).
        if ($this->savedRequest instanceof YiiWebRequest || $this->savedRequest instanceof \yii\console\Request) {
            Craft::$app->set('request', $this->savedRequest);
        } else {
            // Unexpected type — defer to set() with the snapshot anyway.
            Craft::$app->set('request', $this->savedRequest);
        }
        $this->savedRequest = null;
    }
}
