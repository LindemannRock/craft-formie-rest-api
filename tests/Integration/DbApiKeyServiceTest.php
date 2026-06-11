<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use Craft;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\services\ApiKeyService;
use lindemannrock\formierestapi\tests\TestCase;

/**
 * DB-managed API keys: generation, hashing, signing-secret encryption,
 * persistence round-trips, status logic, and bulk operations.
 *
 * @since 3.10.0
 */
final class DbApiKeyServiceTest extends TestCase
{
    protected function cleanupExternalState(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%formierestapi_api_keys}}', ['like', 'name', self::MARKER . '%', false])
            ->execute();
        parent::cleanupExternalState();
    }

    public function testGenerateDbKeyShapeAndUniqueness(): void
    {
        $service = new ApiKeyService();

        $a = $service->generateDbKey();
        $b = $service->generateDbKey();

        self::assertStringStartsWith(ApiKeyService::KEY_PREFIX, $a['plaintext']);
        self::assertSame(4 + 64, strlen($a['plaintext']), 'fra_ + 64 hex chars');
        self::assertSame(substr($a['plaintext'], 0, ApiKeyService::PREFIX_LENGTH), $a['prefix']);
        self::assertSame($service->hashKey($a['plaintext']), $a['hash']);
        self::assertNotSame($a['plaintext'], $b['plaintext'], 'CSPRNG output must differ between calls.');
    }

    public function testHashKeyIsDeterministicAndKeyedBySecurityKey(): void
    {
        $service = new ApiKeyService();
        $plaintext = ApiKeyService::KEY_PREFIX . str_repeat('ab', 32);

        $expected = hash_hmac('sha256', $plaintext, Craft::$app->getConfig()->getGeneral()->securityKey);

        self::assertSame($expected, $service->hashKey($plaintext));
        self::assertSame($service->hashKey($plaintext), $service->hashKey($plaintext));
    }

    public function testFindByPlaintextKeyResolvesOnlyTheRealKey(): void
    {
        $service = FormieRestApi::$plugin->apiKey;
        [$key, $plaintext] = $this->seedDbKey();

        $found = $service->findByPlaintextKey($plaintext);
        self::assertNotNull($found);
        self::assertSame($key->id, $found->id);

        // Same prefix, different body — hash mismatch fails closed.
        $tampered = substr($plaintext, 0, -4) . 'beef';
        self::assertNull($service->findByPlaintextKey($tampered));

        // Unknown prefix and malformed input also fail undifferentiated.
        self::assertNull($service->findByPlaintextKey(ApiKeyService::KEY_PREFIX . str_repeat('0', 64)));
        self::assertNull($service->findByPlaintextKey('short'));
    }

    public function testSigningSecretEncryptDecryptRoundTrip(): void
    {
        $service = new ApiKeyService();
        $secret = $service->generateSigningSecret();

        $encrypted = $service->encryptSigningSecret($secret);

        self::assertNotSame($secret, $encrypted, 'Secret must not be stored in the clear.');
        self::assertStringNotContainsString($secret, $encrypted);
        self::assertSame($secret, $service->decryptSigningSecret($encrypted));

        // Missing / corrupt / tampered ciphertext all decrypt to null (fail closed).
        self::assertNull($service->decryptSigningSecret(null));
        self::assertNull($service->decryptSigningSecret(''));
        self::assertNull($service->decryptSigningSecret('not-base64!!'));
        self::assertNull($service->decryptSigningSecret(base64_encode('tampered-ciphertext')));
    }

    public function testModelSaveLoadRoundTripPreservesAllFields(): void
    {
        [$key] = $this->seedDbKey([
            'requireSignature' => false,
            'canReadSubmissions' => false,
            'allowedForms' => ['contactForm', 'productRating'],
            'ipWhitelist' => ['203.0.113.5', '192.168.1.0/24'],
            'rateLimit' => 250,
        ]);

        $loaded = ApiKey::findById($key->id);

        self::assertNotNull($loaded);
        self::assertSame($key->name, $loaded->name);
        self::assertSame($key->keyPrefix, $loaded->keyPrefix);
        self::assertFalse($loaded->requireSignature);
        self::assertFalse($loaded->canReadSubmissions);
        self::assertSame(['contactForm', 'productRating'], $loaded->allowedForms);
        self::assertSame(['203.0.113.5', '192.168.1.0/24'], $loaded->ipWhitelist);
        self::assertSame(250, $loaded->rateLimit);
        self::assertNotNull($loaded->signingSecretEnc);
    }

    public function testStatusPriorityAndValidity(): void
    {
        [$active] = $this->seedDbKey();
        self::assertSame(ApiKey::STATUS_ACTIVE, $active->getStatus());
        self::assertTrue($active->isStillValid());

        [$expired] = $this->seedDbKey(['validUntil' => new \DateTime('-1 hour', new \DateTimeZone('UTC'))]);
        self::assertSame(ApiKey::STATUS_EXPIRED, $expired->getStatus());
        self::assertFalse($expired->isStillValid());

        // Disabled wins over expired: an operator pause is the intentional state.
        [$disabledExpired] = $this->seedDbKey([
            'enabled' => false,
            'validUntil' => new \DateTime('-1 hour', new \DateTimeZone('UTC')),
        ]);
        self::assertSame(ApiKey::STATUS_DISABLED, $disabledExpired->getStatus());
        self::assertFalse($disabledExpired->isStillValid());
    }

    public function testEnabledKeyRequiresFormBoundary(): void
    {
        $service = FormieRestApi::$plugin->apiKey;
        $generated = $service->generateDbKey();

        $key = new ApiKey();
        $key->name = self::MARKER . 'draftless';
        $key->keyHash = $generated['hash'];
        $key->keyPrefix = $generated['prefix'];
        $key->enabled = true;
        $key->allowedForms = [];

        self::assertFalse($key->save(), 'Enabled key with no form boundary must fail validation.');
        self::assertNotEmpty($key->getErrors('allowedForms'));

        // The same shape is a valid disabled draft.
        $key->enabled = false;
        self::assertTrue($key->save());
    }

    public function testIpWhitelistEntriesAreShapeValidated(): void
    {
        $service = FormieRestApi::$plugin->apiKey;
        $generated = $service->generateDbKey();

        $key = new ApiKey();
        $key->name = self::MARKER . 'badip';
        $key->keyHash = $generated['hash'];
        $key->keyPrefix = $generated['prefix'];
        $key->allowedForms = [ApiKey::ALL_FORMS];
        $key->ipWhitelist = ['not-an-ip', '10.0.0.0/99'];

        self::assertFalse($key->save());
        self::assertNotEmpty($key->getErrors('ipWhitelist'));

        $key->ipWhitelist = ['203.0.113.5', '2001:db8::/32', '10.0.0.0/8'];
        self::assertTrue($key->save(), 'Valid IPv4, IPv6 CIDR, and IPv4 CIDR entries must pass.');
    }

    public function testRecordUsageStampsLastUsedAt(): void
    {
        [$key] = $this->seedDbKey();
        self::assertNull($key->lastUsedAt);

        FormieRestApi::$plugin->apiKey->recordUsage($key);

        self::assertNotNull($key->lastUsedAt, 'Model is stamped in place.');
        $reloaded = ApiKey::findById($key->id);
        self::assertNotNull($reloaded->lastUsedAt, 'Stamp is persisted.');
    }

    public function testBulkSetEnabledAndBulkDelete(): void
    {
        $service = FormieRestApi::$plugin->apiKey;
        [$a] = $this->seedDbKey();
        [$b] = $this->seedDbKey();

        $affected = $service->bulkSetEnabled([$a->id, $b->id, 'junk', -5, $a->id], false);
        self::assertSame(2, $affected, 'Junk and duplicate ids are dropped; both real rows updated.');
        self::assertFalse(ApiKey::findById($a->id)->enabled);
        self::assertFalse(ApiKey::findById($b->id)->enabled);

        self::assertSame(0, $service->bulkSetEnabled([], true), 'Empty selection is a no-op.');

        $deleted = $service->bulkDelete([$a->id, $b->id]);
        self::assertSame(2, $deleted);
        self::assertNull(ApiKey::findById($a->id));
        self::assertNull(ApiKey::findById($b->id));
    }

    /**
     * Seed a persisted DB key. Returns [model, plaintext, signingSecret].
     *
     * @param array<string, mixed> $attributes
     * @return array{0: ApiKey, 1: string, 2: string}
     */
    private function seedDbKey(array $attributes = []): array
    {
        $service = FormieRestApi::$plugin->apiKey;
        $generated = $service->generateDbKey();
        $secret = $service->generateSigningSecret();

        $key = new ApiKey();
        $key->name = self::MARKER . substr($generated['prefix'], 4);
        $key->keyHash = $generated['hash'];
        $key->keyPrefix = $generated['prefix'];
        $key->signingSecretEnc = $service->encryptSigningSecret($secret);
        $key->allowedForms = [ApiKey::ALL_FORMS];

        foreach ($attributes as $name => $value) {
            $key->$name = $value;
        }

        self::assertTrue($key->save(), 'Test key must save: ' . print_r($key->getErrors(), true));

        return [$key, $generated['plaintext'], $secret];
    }
}
