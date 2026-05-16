<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Stubs;

use craft\console\Request as CraftConsoleRequest;
use yii\web\HeaderCollection;

/**
 * Console-mode request stub that exposes the accessors
 * {@see \lindemannrock\formierestapi\services\SecurityService::validateRequestSignature()}
 * and {@see \lindemannrock\formierestapi\services\SecurityService::validateIpWhitelist()}
 * read: `getHeaders()`, `getMethod()`, `getUrl()`, `getRawBody()`, `getUserIP()`,
 * `getUserAgent()`, `getReferrer()`.
 *
 * Extends Craft's console `Request` directly (rather than the shared
 * `StubConsoleRequest`, which is `final`) so `getIsConsoleRequest()` stays
 * truthful while we layer the web-only methods this plugin needs.
 *
 * Plugin-local: stays here until a second consumer needs the same shape.
 */
final class StubApiRequest extends CraftConsoleRequest
{
    private HeaderCollection $headerCollection;

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $config
     */
    public function __construct(
        public string $apiMethod = 'GET',
        public string $apiUrl = '/api/v1/formie/forms',
        public string $apiRawBody = '',
        array $headers = [],
        public string $userIp = '203.0.113.42',
        public string $userAgent = 'Mozilla/5.0 (Test) LindemannRockStub/1.0',
        public ?string $referrer = 'https://example.com/some/page',
        array $config = [],
    ) {
        parent::__construct($config);

        $collection = new HeaderCollection();
        foreach ($headers as $name => $value) {
            $collection->set($name, $value);
        }
        $this->headerCollection = $collection;
    }

    public function getHeaders(): HeaderCollection
    {
        return $this->headerCollection;
    }

    public function getMethod(): string
    {
        return $this->apiMethod;
    }

    public function getUrl(): string
    {
        return $this->apiUrl;
    }

    public function getRawBody(): string
    {
        return $this->apiRawBody;
    }

    public function getUserIP(): ?string
    {
        return $this->userIp;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }
}
