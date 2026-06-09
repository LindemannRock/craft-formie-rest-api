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
 * Pins the contract for {@see SecurityService::validateRequestSignature()}.
 *
 * The audit-1.5 HMAC rollout standardised on the canonical base
 * `METHOD\nPATH\nTIMESTAMP\nBODY`, signed with HMAC-SHA256 and compared via
 * `hash_equals`. Headers are `X-Signature` (hex) and `X-Timestamp` (unix
 * epoch seconds). Skew window is 5 minutes either side of `time()`.
 *
 * These tests pin:
 *  - valid signature → true (the canonical-base computation is correct)
 *  - missing signing secret → false (opt-in is per-key)
 *  - missing headers (either one) → false (defensive presence checks)
 *  - expired timestamp → false (replay-protection window enforced)
 *  - tampered body → false (`hash_equals` actually fires)
 *  - query-sorted signature → true even when the request arrives unsorted
 *    (CDN/proxy query-order normalisation is tolerated)
 *  - received-order signature → still true (strictly additive, no regression)
 *  - a third arbitrary query order → false (only received + sorted are accepted)
 */
final class SecurityServiceSignatureTest extends TestCase
{
    private const SECRET = 'shh_test_signing_secret_for_pinning_hmac_base';

    public function testValidSignatureReturnsTrue(): void
    {
        $timestamp = (string) time();
        $method = 'POST';
        $url = '/api/v1/formie/submissions?formHandle=contact';
        $body = '{"name":"alice"}';

        $stub = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $url,
            apiRawBody: $body,
            headers: [
                'X-Signature' => $this->buildSignature(self::SECRET, $method, $url, $timestamp, $body),
                'X-Timestamp' => $timestamp,
            ],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();
        $this->assertTrue(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'Signature computed over METHOD\nPATH\nTIMESTAMP\nBODY with the per-key secret must validate.',
        );
    }

    public function testMissingSigningSecretReturnsFalse(): void
    {
        // Opt-in contract: if a key's data has no signing secret, the method
        // returns false (caller treats that as "HMAC not configured for this
        // key" and uses requireSignature to decide whether to actually 401).
        $stub = new StubApiRequest(
            headers: ['X-Signature' => 'irrelevant', 'X-Timestamp' => (string) time()],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();

        $this->assertFalse($service->validateRequestSignature([]));
        $this->assertFalse($service->validateRequestSignature(['signingSecret' => null]));
        $this->assertFalse($service->validateRequestSignature(['signingSecret' => '']));
    }

    public function testMissingHeadersReturnFalse(): void
    {
        $service = new SecurityService();

        // No signature header.
        $stubNoSig = new StubApiRequest(headers: ['X-Timestamp' => (string) time()]);
        $this->installRequestStub($stubNoSig);
        $this->assertFalse(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'Missing X-Signature header must reject.',
        );

        // No timestamp header.
        $stubNoTs = new StubApiRequest(headers: ['X-Signature' => 'deadbeef']);
        $this->installRequestStub($stubNoTs);
        $this->assertFalse(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'Missing X-Timestamp header must reject.',
        );
    }

    public function testExpiredTimestampReturnsFalse(): void
    {
        // 10 minutes in the past — well beyond the 5-minute skew window.
        $oldTimestamp = (string) (time() - 600);
        $method = 'GET';
        $url = '/api/v1/formie/forms';

        $stub = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $url,
            apiRawBody: '',
            headers: [
                'X-Signature' => $this->buildSignature(self::SECRET, $method, $url, $oldTimestamp, ''),
                'X-Timestamp' => $oldTimestamp,
            ],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();
        $this->assertFalse(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'A correctly-signed-but-expired request must still be rejected by the replay window.',
        );

        // Symmetrical: 10 minutes in the FUTURE — also outside the window.
        $futureTimestamp = (string) (time() + 600);
        $stubFuture = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $url,
            headers: [
                'X-Signature' => $this->buildSignature(self::SECRET, $method, $url, $futureTimestamp, ''),
                'X-Timestamp' => $futureTimestamp,
            ],
        );
        $this->installRequestStub($stubFuture);
        $this->assertFalse(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'Far-future timestamps must also be rejected (defends against clock-skew abuse).',
        );
    }

    public function testTamperedBodyFailsHashEquals(): void
    {
        $timestamp = (string) time();
        $method = 'POST';
        $url = '/api/v1/formie/submissions';
        $intendedBody = '{"name":"alice"}';
        $tamperedBody = '{"name":"mallory"}';

        // Client signs the intended body, but the request body has been
        // swapped on the wire. With a correct hash_equals comparison the
        // signature must NOT validate.
        $signatureOverIntended = $this->buildSignature(self::SECRET, $method, $url, $timestamp, $intendedBody);

        $stub = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $url,
            apiRawBody: $tamperedBody,
            headers: [
                'X-Signature' => $signatureOverIntended,
                'X-Timestamp' => $timestamp,
            ],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();
        $this->assertFalse(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'A signature computed over a different body must fail hash_equals.',
        );
    }

    public function testSortedQuerySignatureValidatesWhenRequestArrivesUnsorted(): void
    {
        // A client signs the query in alphabetical order (the documented rule,
        // and what a CDN such as Cloudflare normalises to). Even if the request
        // reaches us with the params in a different order, the signature must
        // still validate — the server canonicalises the query before comparing.
        $timestamp = (string) time();
        $method = 'GET';
        $receivedUrl = '/api/v1/formie/submissions?limit=100&offset=0&formHandle=contact';
        $sortedUrl = '/api/v1/formie/submissions?formHandle=contact&limit=100&offset=0';

        $stub = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $receivedUrl,
            apiRawBody: '',
            headers: [
                'X-Signature' => $this->buildSignature(self::SECRET, $method, $sortedUrl, $timestamp, ''),
                'X-Timestamp' => $timestamp,
            ],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();
        $this->assertTrue(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'A signature over the alphabetically-sorted query must validate even when the request arrives with params in a different order.',
        );
    }

    public function testReceivedOrderSignatureStillValidates(): void
    {
        // Strictly additive: a signature computed over the exact (unsorted) order
        // the request arrives in must still validate, so clients that sign what
        // they send are not broken.
        $timestamp = (string) time();
        $method = 'GET';
        $url = '/api/v1/formie/submissions?limit=100&offset=0&formHandle=contact';

        $stub = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $url,
            apiRawBody: '',
            headers: [
                'X-Signature' => $this->buildSignature(self::SECRET, $method, $url, $timestamp, ''),
                'X-Timestamp' => $timestamp,
            ],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();
        $this->assertTrue(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'A signature over the URL exactly as received must still validate (backward compatible).',
        );
    }

    public function testUnrelatedQueryOrderStillFails(): void
    {
        // Only two forms are accepted: the URL as received and its query-sorted
        // form. A signature over some third arbitrary ordering must still fail —
        // canonicalisation must not degrade into "accept any order".
        $timestamp = (string) time();
        $method = 'GET';
        $receivedUrl = '/api/v1/formie/submissions?limit=100&offset=0&formHandle=contact';
        // Neither the received order nor the alphabetically-sorted order.
        $otherOrderUrl = '/api/v1/formie/submissions?offset=0&formHandle=contact&limit=100';

        $stub = new StubApiRequest(
            apiMethod: $method,
            apiUrl: $receivedUrl,
            apiRawBody: '',
            headers: [
                'X-Signature' => $this->buildSignature(self::SECRET, $method, $otherOrderUrl, $timestamp, ''),
                'X-Timestamp' => $timestamp,
            ],
        );
        $this->installRequestStub($stub);

        $service = new SecurityService();
        $this->assertFalse(
            $service->validateRequestSignature(['signingSecret' => self::SECRET]),
            'A signature over an arbitrary query order (neither received nor sorted) must not validate.',
        );
    }
}
