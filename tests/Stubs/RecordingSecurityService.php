<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Stubs;

use lindemannrock\formierestapi\services\SecurityService;

/**
 * Records controller security-policy calls and structured access events.
 *
 * @since 3.10.2
 */
final class RecordingSecurityService extends SecurityService
{
    public bool $signatureValid = true;

    public bool $ipAllowed = true;

    public bool $rateAllowed = true;

    public int $signatureChecks = 0;

    /** @var list<array{apiKey: string, endpoint: string, params: array<mixed>, responseCode: int}> */
    public array $accessEvents = [];

    public function validateRequestSignature(array $apiKeyData): bool
    {
        $this->signatureChecks++;
        return $this->signatureValid;
    }

    public function validateIpWhitelist(array $apiKeyData): bool
    {
        return $this->ipAllowed;
    }

    public function checkRateLimit(string $apiKey, array $apiKeyData): bool
    {
        return $this->rateAllowed;
    }

    public function getRateLimitHeaders(string $apiKey, array $apiKeyData): array
    {
        return [
            'X-RateLimit-Limit' => '100',
            'X-RateLimit-Remaining' => $this->rateAllowed ? '99' : '0',
            'X-RateLimit-Reset' => (string) (time() + 3600),
        ];
    }

    public function logApiAccess(string $apiKey, string $endpoint, array $params, int $responseCode): void
    {
        $this->accessEvents[] = [
            'apiKey' => $apiKey,
            'endpoint' => $endpoint,
            'params' => $params,
            'responseCode' => $responseCode,
        ];
    }
}
