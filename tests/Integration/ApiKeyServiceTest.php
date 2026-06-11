<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\services\ApiKeyService;
use lindemannrock\formierestapi\tests\TestCase;

/**
 * Pins the contract for {@see ApiKeyService::getValidApiKeys()} /
 * {@see ApiKeyService::validateApiKey()}.
 *
 * Covers four audit-driven contracts:
 *
 *  - Audit 1.2: the test key has no hardcoded fallback. It only registers
 *    when devMode is on AND `FORMIE_API_KEY_TEST` is a non-empty string.
 *  - Audit 1.5: HMAC opt-in is by *signing-secret presence*, not env-var
 *    name. A key registered without a paired secret env must have
 *    `requireSignature => false`.
 *  - IP-whitelist env parsing: comma-separated CIDR/IP entries are
 *    trimmed and empty entries dropped.
 *  - Audit 5.2: `getValidApiKeys()` memoizes per-instance, so a single
 *    request reads the env vars exactly once.
 */
final class ApiKeyServiceTest extends TestCase
{
    public function testPrimaryKeyResolvesAndHmacIsOnlyOnWhenSigningSecretPresent(): void
    {
        $primary = self::MARKER . 'primary';
        $this->setEnv('FORMIE_API_KEY', $primary);
        $this->setEnv('FORMIE_API_SIGNING_SECRET', null);

        $service = new ApiKeyService();
        $keys = $service->getValidApiKeys();

        $this->assertArrayHasKey($primary, $keys, 'Primary key must register when env var is set.');
        $this->assertSame('Primary API Key', $keys[$primary]['name']);
        $this->assertContains('read_forms', $keys[$primary]['permissions']);
        $this->assertContains('read_submissions', $keys[$primary]['permissions']);
        // create_submissions is intentionally absent: there is no submission-creation
        // endpoint, so the scope would be unenforced metadata (audit P13.1).
        $this->assertNotContains('create_submissions', $keys[$primary]['permissions']);
        $this->assertNull($keys[$primary]['signingSecret'], 'No secret env means no signing secret.');
        $this->assertFalse($keys[$primary]['requireSignature'], 'requireSignature must be off when no signing secret is configured.');

        // Now flip on the signing secret and assert the inverse for a fresh
        // service instance (the original is memoized).
        $this->setEnv('FORMIE_API_SIGNING_SECRET', 'shh_supersecret');
        $fresh = new ApiKeyService();
        $freshKeys = $fresh->getValidApiKeys();

        $this->assertSame('shh_supersecret', $freshKeys[$primary]['signingSecret']);
        $this->assertTrue($freshKeys[$primary]['requireSignature'], 'requireSignature flips on as soon as a signing secret env is present.');
    }

    public function testTestKeyRequiresDevModeAndNonEmptyEnvVar(): void
    {
        // Audit 1.2: with the hardcoded `'test_key_dev_only'` fallback gone,
        // an empty or unset FORMIE_API_KEY_TEST must produce NO test key, even
        // when devMode is on.
        $primary = self::MARKER . 'primary';
        $this->setEnv('FORMIE_API_KEY', $primary);
        $this->setEnv('FORMIE_API_KEY_TEST', null);

        // devMode is on in the local DDEV install; not stubbing it directly,
        // we just rely on the live config. Sanity-check it before asserting
        // the conditional below.
        $this->assertTrue(
            \Craft::$app->getConfig()->getGeneral()->devMode,
            'Test harness assumes devMode is on; if this fires, set devMode in dev to exercise the test-key gate.',
        );

        $serviceUnset = new ApiKeyService();
        $keysUnset = $serviceUnset->getValidApiKeys();
        foreach (array_keys($keysUnset) as $registeredKey) {
            $this->assertNotSame('Development Test Key', $keysUnset[$registeredKey]['name']);
        }
        $this->assertArrayNotHasKey('test_key_dev_only', $keysUnset, 'No literal fallback key may exist.');

        // Empty string env: still rejected (must be is_string && !==
        // empty).
        $this->setEnv('FORMIE_API_KEY_TEST', '');
        $serviceEmpty = new ApiKeyService();
        $keysEmpty = $serviceEmpty->getValidApiKeys();
        foreach (array_values($keysEmpty) as $data) {
            $this->assertNotSame('Development Test Key', $data['name']);
        }

        // Now a real test key value — should register.
        $testKey = self::MARKER . 'test';
        $this->setEnv('FORMIE_API_KEY_TEST', $testKey);
        $serviceSet = new ApiKeyService();
        $keysSet = $serviceSet->getValidApiKeys();
        $this->assertArrayHasKey($testKey, $keysSet);
        $this->assertSame('Development Test Key', $keysSet[$testKey]['name']);
        $this->assertSame('development', $keysSet[$testKey]['environment']);
    }

    public function testIpWhitelistEnvParsesAndTrimsEntriesAndDropsEmpties(): void
    {
        $primary = self::MARKER . 'primary';
        $this->setEnv('FORMIE_API_KEY', $primary);
        // Mixed valid IPv4 + CIDR + IPv6 + whitespace-padded + empty entries.
        $this->setEnv('FORMIE_API_IP_WHITELIST', '  203.0.113.5  ,192.168.1.0/24,, 2001:db8::/32 ,  ');

        $service = new ApiKeyService();
        $keys = $service->getValidApiKeys();

        $this->assertArrayHasKey($primary, $keys);
        $this->assertSame(
            ['203.0.113.5', '192.168.1.0/24', '2001:db8::/32'],
            $keys[$primary]['ipWhitelist'],
            'Whitespace must be trimmed; empty entries dropped; order preserved.',
        );

        // Unset env → empty array (no restriction).
        $this->setEnv('FORMIE_API_IP_WHITELIST', null);
        $serviceNoList = new ApiKeyService();
        $this->assertSame([], $serviceNoList->getValidApiKeys()[$primary]['ipWhitelist']);
    }

    public function testValidateApiKeyHandlesNullUnknownAndKnownKeys(): void
    {
        $primary = self::MARKER . 'primary';
        $this->setEnv('FORMIE_API_KEY', $primary);

        $service = new ApiKeyService();

        $this->assertFalse($service->validateApiKey(null), 'Null key must be rejected.');
        $this->assertFalse($service->validateApiKey(''), 'Empty key must be rejected.');
        $this->assertFalse(
            $service->validateApiKey(self::MARKER . 'never_registered'),
            'Unknown key must be rejected.',
        );

        $known = $service->validateApiKey($primary);
        $this->assertIsArray($known);
        $this->assertSame('Primary API Key', $known['name']);
    }

    public function testGetValidApiKeysMemoizesAcrossCalls(): void
    {
        // Audit 5.2: cachedKeys is populated on first read. Subsequent calls
        // must return the same snapshot even if env changes mid-request.
        $first = self::MARKER . 'first';
        $this->setEnv('FORMIE_API_KEY', $first);

        $service = new ApiKeyService();
        $snapshot1 = $service->getValidApiKeys();

        // Change the env behind the service's back.
        $second = self::MARKER . 'second';
        $this->setEnv('FORMIE_API_KEY', $second);

        $snapshot2 = $service->getValidApiKeys();

        $this->assertArrayHasKey($first, $snapshot1);
        $this->assertArrayHasKey($first, $snapshot2, 'Memoized snapshot must still contain the original key.');
        $this->assertArrayNotHasKey($second, $snapshot2, 'Memoized snapshot must NOT pick up post-resolution env changes.');
    }
}
