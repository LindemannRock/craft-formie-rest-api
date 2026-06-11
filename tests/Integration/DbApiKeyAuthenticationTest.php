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
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;

/**
 * `validateApiKey()` with DB-managed keys: env-key coexistence, permission
 * hydration from the per-key toggles, fail-closed rejection of disabled /
 * expired / unknown keys, and end-to-end HMAC signature validation using a
 * DB key's decrypted signing secret.
 *
 * @since 3.10.0
 */
final class DbApiKeyAuthenticationTest extends TestCase
{
    protected function cleanupExternalState(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%formierestapi_api_keys}}', ['like', 'name', self::MARKER . '%', false])
            ->execute();
        parent::cleanupExternalState();
    }

    public function testEnvKeyStillResolvesBeforeDbLookup(): void
    {
        $envKey = self::MARKER . 'env_primary';
        $this->setEnv('FORMIE_API_KEY', $envKey);

        $data = FormieRestApi::$plugin->apiKey->validateApiKey($envKey);

        self::assertIsArray($data);
        self::assertSame('Primary API Key', $data['name'], 'Env keys keep working unchanged alongside DB keys.');
        self::assertArrayNotHasKey('dbKey', $data);
    }

    public function testValidDbKeyHydratesIntoApiKeyDataShape(): void
    {
        [$key, $plaintext, $secret] = $this->seedDbKey();

        $data = FormieRestApi::$plugin->apiKey->validateApiKey($plaintext);

        self::assertIsArray($data);
        self::assertSame($key->name, $data['name']);
        self::assertSame(['read_forms', 'read_submissions'], $data['permissions']);
        self::assertSame($secret, $data['signingSecret'], 'Secret decrypts back to the generated value.');
        self::assertTrue($data['requireSignature']);
        self::assertSame([ApiKey::ALL_FORMS], $data['allowedForms']);
        self::assertInstanceOf(ApiKey::class, $data['dbKey']);
    }

    public function testSubmissionsPermissionFollowsToggle(): void
    {
        [, $plaintext] = $this->seedDbKey(['canReadSubmissions' => false]);

        $data = FormieRestApi::$plugin->apiKey->validateApiKey($plaintext);

        self::assertSame(['read_forms'], $data['permissions'], 'Forms-only key carries no read_submissions scope.');
    }

    public function testDisabledExpiredAndUnknownKeysFailUndifferentiated(): void
    {
        $service = FormieRestApi::$plugin->apiKey;

        [, $disabledPlaintext] = $this->seedDbKey(['enabled' => false]);
        self::assertFalse($service->validateApiKey($disabledPlaintext));

        [, $expiredPlaintext] = $this->seedDbKey([
            'validUntil' => new \DateTime('-1 minute', new \DateTimeZone('UTC')),
        ]);
        self::assertFalse($service->validateApiKey($expiredPlaintext));

        self::assertFalse($service->validateApiKey('fra_' . str_repeat('0', 64)));
        self::assertFalse($service->validateApiKey(''));
        self::assertFalse($service->validateApiKey(null));
    }

    public function testSignatureRequirementFailsClosedOnCorruptSecret(): void
    {
        [$key, $plaintext] = $this->seedDbKey();

        // Simulate at-rest corruption / tampering of the encrypted secret.
        Craft::$app->getDb()->createCommand()
            ->update('{{%formierestapi_api_keys}}', ['signingSecretEnc' => base64_encode('tampered')], ['id' => $key->id])
            ->execute();

        $data = FormieRestApi::$plugin->apiKey->validateApiKey($plaintext);

        self::assertIsArray($data);
        self::assertNull($data['signingSecret']);
        self::assertFalse(
            $data['requireSignature'],
            'requireSignature reports false when the secret is unrecoverable — the signature check itself then fails closed.',
        );
        self::assertFalse(
            FormieRestApi::$plugin->security->validateRequestSignature($data),
            'No recoverable secret → signature validation can never pass.',
        );
    }

    public function testHmacSignatureValidatesEndToEndWithDbKeySecret(): void
    {
        [, $plaintext, $secret] = $this->seedDbKey();
        $data = FormieRestApi::$plugin->apiKey->validateApiKey($plaintext);

        $url = '/api/v1/formie/submissions?formHandle=contact&limit=10';
        $timestamp = (string) time();

        $this->installRequestStub(new StubApiRequest(
            apiMethod: 'GET',
            apiUrl: $url,
            headers: [
                'X-Timestamp' => $timestamp,
                'X-Signature' => $this->buildSignature($secret, 'GET', $url, $timestamp, ''),
            ],
        ));
        self::assertTrue(FormieRestApi::$plugin->security->validateRequestSignature($data));

        // Signature computed with the wrong secret is rejected.
        $this->installRequestStub(new StubApiRequest(
            apiMethod: 'GET',
            apiUrl: $url,
            headers: [
                'X-Timestamp' => $timestamp,
                'X-Signature' => $this->buildSignature('wrong-secret', 'GET', $url, $timestamp, ''),
            ],
        ));
        self::assertFalse(FormieRestApi::$plugin->security->validateRequestSignature($data));
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
