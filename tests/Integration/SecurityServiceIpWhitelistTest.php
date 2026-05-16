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
use lindemannrock\formierestapi\tests\Stubs\StubApiRequest;
use lindemannrock\formierestapi\tests\TestCase;

/**
 * Pins the contract for {@see SecurityService::validateIpWhitelist()} (and
 * indirectly the private `ipMatches()`).
 *
 * Audit history this guards:
 *  - 1.6: the whitelist is checked at all (was previously implemented but
 *    never invoked).
 *  - 1.7: IPv4-only matching via `ip2long()` replaced with `inet_pton()` so
 *    IPv6 + CIDR work and cross-family entries can't accidentally match.
 *
 * The tests exercise the public `validateIpWhitelist()` so we don't reach
 * into the private matcher. Each scenario pins a specific entry-vs-client
 * combination that has caused real-world bugs in similar middleware
 * elsewhere in the workspace.
 */
final class SecurityServiceIpWhitelistTest extends TestCase
{
    public function testEmptyWhitelistAllowsAnyClient(): void
    {
        $this->installRequestStub(new StubApiRequest(userIp: '203.0.113.42'));

        $service = new SecurityService();

        $this->assertTrue($service->validateIpWhitelist([]), 'No apiKeyData at all → allow.');
        $this->assertTrue($service->validateIpWhitelist(['ipWhitelist' => []]), 'Empty list → no restriction.');
    }

    public function testIpv4ClientMatchesIpv4CidrAndExactEntry(): void
    {
        $this->installRequestStub(new StubApiRequest(userIp: '192.168.1.42'));

        $service = new SecurityService();

        $this->assertTrue(
            $service->validateIpWhitelist(['ipWhitelist' => ['192.168.1.0/24']]),
            'Client 192.168.1.42 falls inside 192.168.1.0/24.',
        );
        $this->assertTrue(
            $service->validateIpWhitelist(['ipWhitelist' => ['192.168.1.42']]),
            'Exact-IP entry must match identical client.',
        );
        $this->assertTrue(
            $service->validateIpWhitelist(['ipWhitelist' => ['10.0.0.0/8', '192.168.0.0/16']]),
            'Multi-entry whitelist matches on the second (broader) CIDR.',
        );
    }

    public function testIpv6ClientMatchesIpv6Cidr(): void
    {
        $this->installRequestStub(new StubApiRequest(userIp: '2001:db8::1'));

        $service = new SecurityService();

        $this->assertTrue(
            $service->validateIpWhitelist(['ipWhitelist' => ['2001:db8::/32']]),
            'IPv6 client must match an IPv6 /32 CIDR.',
        );
    }

    public function testCrossFamilyEntriesNeverMatch(): void
    {
        // Pins the audit 1.7 fix: an IPv4 client must NOT match an IPv6
        // catch-all or IPv6 CIDR (and vice versa).
        $this->installRequestStub(new StubApiRequest(userIp: '203.0.113.42'));
        $service = new SecurityService();

        $this->assertFalse(
            $service->validateIpWhitelist(['ipWhitelist' => ['::/0']]),
            'IPv4 client must NOT match the IPv6 catch-all "::/0".',
        );
        $this->assertFalse(
            $service->validateIpWhitelist(['ipWhitelist' => ['2001:db8::/32']]),
            'IPv4 client must NOT match an IPv6 CIDR.',
        );

        // Inverse direction.
        $this->installRequestStub(new StubApiRequest(userIp: '2001:db8::1'));
        $this->assertFalse(
            $service->validateIpWhitelist(['ipWhitelist' => ['0.0.0.0/0']]),
            'IPv6 client must NOT match the IPv4 catch-all "0.0.0.0/0".',
        );
    }

    public function testNonMatchingClientAndUnparseableEntriesAreRejected(): void
    {
        $this->installRequestStub(new StubApiRequest(userIp: '203.0.113.42'));
        $service = new SecurityService();

        // No matching CIDR.
        $this->assertFalse(
            $service->validateIpWhitelist(['ipWhitelist' => ['10.0.0.0/8', '172.16.0.0/12']]),
            'Client outside every CIDR must be rejected.',
        );

        // Unparseable entries fail closed without throwing.
        $this->assertFalse(
            $service->validateIpWhitelist(['ipWhitelist' => ['not-an-ip', '999.999.999.999/24']]),
            'Garbage entries must reject — never silently allow.',
        );
    }
}
