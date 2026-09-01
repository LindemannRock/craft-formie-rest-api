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
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use lindemannrock\formierestapi\controllers\SettingsController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\tests\Stubs\DiagnosticActionRequest;
use lindemannrock\formierestapi\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use yii\web\Response;

/**
 * @since 3.10.2
 */
#[CoversClass(SettingsController::class)]
final class SettingsDiagnosticRequestTest extends TestCase
{
    private object $originalRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRequest = Craft::$app->getRequest();
    }

    protected function tearDown(): void
    {
        Craft::$app->set('request', $this->originalRequest);
        parent::tearDown();
    }

    public function testMissingApiKeyRejectsWithoutSendingARequest(): void
    {
        $history = [];
        $client = $this->mockClient(new GuzzleResponse(200), $history);

        $response = $this->runDiagnostic($client, [
            'testPastedKey' => '',
            'testEndpoint' => 'forms',
        ]);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Paste an API key to test.', $response->data['error'] ?? null);
        self::assertCount(0, $history);
    }

    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('invalidDetailInputProvider')]
    public function testInvalidDetailInputRejectsBeforeOutboundHttp(array $input, string $expectedError): void
    {
        $history = [];
        $client = $this->mockClient(new GuzzleResponse(200), $history);

        $response = $this->runDiagnostic($client, array_merge([
            'testPastedKey' => self::MARKER . 'key',
        ], $input));

        self::assertSame(422, $response->getStatusCode());
        self::assertSame($expectedError, $response->data['error'] ?? null);
        self::assertCount(0, $history, 'Locally invalid detail input must not send an HTTP request.');
    }

    /**
     * @return iterable<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidDetailInputProvider(): iterable
    {
        $idError = 'Enter a positive integer ID.';
        foreach (['', ' ', 'abc', '0', '-1', '1.5', '01', [], ['1']] as $index => $value) {
            yield "form ID invalid {$index}" => [[
                'testEndpoint' => 'form-id',
                'testId' => $value,
            ], $idError];
            yield "submission ID invalid {$index}" => [[
                'testEndpoint' => 'submission-id',
                'testId' => $value,
            ], $idError];
        }

        $handleError = 'Enter a valid form handle.';
        foreach (['', ' ', 'contact form', 'contact/form', 'contact.form', [], ['contact']] as $index => $value) {
            yield "form handle invalid {$index}" => [[
                'testEndpoint' => 'form-handle',
                'testHandle' => $value,
            ], $handleError];
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('validEndpointProvider')]
    public function testValidEndpointChoicesPreserveTheirSelectedUrl(array $input, string $expectedPath): void
    {
        $history = [];
        $client = $this->mockClient(new GuzzleResponse(200, ['Content-Type' => 'application/json'], '{"success":true}'), $history);

        $response = $this->runDiagnostic($client, array_merge([
            'testPastedKey' => self::MARKER . 'key',
        ], $input));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(200, $response->data['status'] ?? null);
        self::assertCount(1, $history);
        self::assertSame($expectedPath, $history[0]['request']->getUri()->getPath() . ($history[0]['request']->getUri()->getQuery() !== '' ? '?' . $history[0]['request']->getUri()->getQuery() : ''));
    }

    /**
     * @return iterable<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function validEndpointProvider(): iterable
    {
        yield 'forms list' => [['testEndpoint' => 'forms'], '/api/v1/formie/forms'];
        yield 'submissions list' => [['testEndpoint' => 'submissions'], '/api/v1/formie/submissions'];
        yield 'boundary form ID' => [['testEndpoint' => 'form-id', 'testId' => '1'], '/api/v1/formie/forms/1'];
        yield 'ordinary form ID' => [['testEndpoint' => 'form-id', 'testId' => '42'], '/api/v1/formie/forms/42'];
        yield 'ordinary form handle' => [['testEndpoint' => 'form-handle', 'testHandle' => 'contact_form-2'], '/api/v1/formie/forms/contact_form-2'];
        yield 'boundary submission ID' => [['testEndpoint' => 'submission-id', 'testId' => '1'], '/api/v1/formie/submissions/1'];
        yield 'ordinary submission ID' => [['testEndpoint' => 'submission-id', 'testId' => '42'], '/api/v1/formie/submissions/42'];
    }

    #[DataProvider('upstreamStatusProvider')]
    public function testUpstreamHttpResponsesRemainDiagnosticResults(int $status): void
    {
        $body = '{"upstreamStatus":' . $status . '}';
        $history = [];
        $client = $this->mockClient(new GuzzleResponse($status, ['X-Upstream' => 'preserved'], $body), $history);

        $response = $this->runDiagnostic($client, [
            'testPastedKey' => self::MARKER . 'key',
            'testEndpoint' => 'forms',
        ]);

        self::assertSame(200, $response->getStatusCode(), 'Upstream non-2xx is a renderable diagnostic result, not a tool failure.');
        self::assertSame($status, $response->data['status'] ?? null);
        self::assertSame('preserved', $response->data['headers']['X-Upstream'] ?? null);
        self::assertStringContainsString((string) $status, (string) ($response->data['body'] ?? ''));
        self::assertArrayNotHasKey('error', $response->data);
    }

    /** @return iterable<string, array{0: int}> */
    public static function upstreamStatusProvider(): iterable
    {
        foreach ([200, 400, 401, 403, 404, 429] as $status) {
            yield (string) $status => [$status];
        }
    }

    #[DataProvider('transportFailureProvider')]
    public function testTransportFailuresRejectWithBoundedCredentialSafeDetail(string $kind, string $message): void
    {
        $key = self::MARKER . 'plaintext-key';
        $secret = self::MARKER . 'signing-secret';
        $request = new Request('GET', 'https://example.test/api/v1/formie/forms');
        $exception = $kind === 'client'
            ? new RequestException($message . ' ' . $key . ' ' . $secret, $request)
            : new ConnectException($message . ' ' . $key . ' ' . $secret, $request);
        $history = [];
        $client = $this->mockClient($exception, $history);

        $response = $this->runDiagnostic($client, [
            'testPastedKey' => $key,
            'testPastedSecret' => $secret,
            'testEndpoint' => 'forms',
        ]);

        $error = (string) ($response->data['error'] ?? '');
        self::assertSame(502, $response->getStatusCode());
        self::assertStringContainsString($message, $error);
        self::assertStringNotContainsString($key, $error);
        self::assertStringNotContainsString($secret, $error);
        self::assertLessThanOrEqual(340, mb_strlen($error));
    }

    /** @return iterable<string, array{0: string, 1: string}> */
    public static function transportFailureProvider(): iterable
    {
        yield 'refused connection' => ['connect', 'Connection refused'];
        yield 'DNS' => ['connect', 'Could not resolve host'];
        yield 'TLS' => ['connect', 'TLS certificate verification failed'];
        yield 'timeout' => ['connect', 'Operation timed out'];
        yield 'client exception' => ['client', 'HTTP client rejected the request'];
    }

    public function testUnexpectedClientResponseFailureUsesGenericToolError(): void
    {
        $responseMessage = self::MARKER . 'unexpected-internal-detail';
        $upstream = $this->createMock(ResponseInterface::class);
        $upstream->method('getHeaders')->willThrowException(new \RuntimeException($responseMessage));
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('request')->willReturn($upstream);

        $response = $this->runDiagnostic($client, [
            'testPastedKey' => self::MARKER . 'key',
            'testEndpoint' => 'forms',
        ]);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('The diagnostic request could not be completed.', $response->data['error'] ?? null);
        self::assertStringNotContainsString($responseMessage, (string) ($response->data['error'] ?? ''));
    }

    public function testUnsignedRequestOutlineUsesOnlySentHeaderPlaceholders(): void
    {
        $history = [];
        $client = $this->mockClient(new GuzzleResponse(200, [], '{}'), $history);
        $key = self::MARKER . 'plaintext-key';

        $response = $this->runDiagnostic($client, [
            'testPastedKey' => $key,
            'testEndpoint' => 'submissions',
            'testFormHandle' => 'contact',
            'testLimit' => '10',
            'testOffset' => '0',
        ]);

        $outline = (string) ($response->data['requestOutline'] ?? '');
        $url = (string) $history[0]['request']->getUri();
        self::assertSame("GET {$url}\nAccept: application/json\nX-API-Key: <API_KEY>", $outline);
        self::assertStringNotContainsString($key, $outline);
        self::assertStringNotContainsString('X-Timestamp', $outline);
        self::assertStringNotContainsString('X-Signature', $outline);
    }

    public function testSignedRequestOutlineMatchesSentHeaderShapeWithoutLiveCredentials(): void
    {
        $history = [];
        $client = $this->mockClient(new GuzzleResponse(200, [], '{}'), $history);
        $key = self::MARKER . 'plaintext-key';
        $secret = self::MARKER . 'signing-secret';

        $response = $this->runDiagnostic($client, [
            'testPastedKey' => $key,
            'testPastedSecret' => $secret,
            'testEndpoint' => 'submissions',
            'testFormHandle' => 'contact',
            'testLimit' => '10',
            'testOffset' => '0',
        ]);

        $request = $history[0]['request'];
        $outline = (string) ($response->data['requestOutline'] ?? '');
        $url = (string) $request->getUri();
        self::assertSame("GET {$url}\nAccept: application/json\nX-API-Key: <API_KEY>\nX-Timestamp: <TIMESTAMP>\nX-Signature: <SIGNATURE>", $outline);
        self::assertNotSame('', $request->getHeaderLine('X-Timestamp'));
        self::assertNotSame('', $request->getHeaderLine('X-Signature'));
        self::assertStringNotContainsString($key, $outline);
        self::assertStringNotContainsString($secret, $outline);
        self::assertStringNotContainsString($request->getHeaderLine('X-Timestamp'), $outline);
        self::assertStringNotContainsString($request->getHeaderLine('X-Signature'), $outline);
    }

    public function testBrowserRendererChecksToolErrorsAndMalformedShapesBeforeRendering(): void
    {
        $template = (string) file_get_contents(dirname(__DIR__, 2) . '/src/templates/settings/test.twig');
        $errorGuard = strpos($template, 'if (data?.error)');
        $shapeGuard = strpos($template, "typeof data?.status !== 'number'");
        $headerRendering = strpos($template, 'Object.entries(data.headers)');

        self::assertIsInt($errorGuard);
        self::assertIsInt($shapeGuard);
        self::assertIsInt($headerRendering);
        self::assertLessThan($headerRendering, $errorGuard);
        self::assertLessThan($headerRendering, $shapeGuard);
        self::assertStringContainsString('e.response?.data?.error || e.message', $template);
        self::assertStringContainsString('resultRequestOutline', $template);
        self::assertStringNotContainsString('Equivalent curl', $template);
        self::assertStringNotContainsString('data.curl', $template);
    }

    /**
     * @param ResponseInterface|\Throwable $result
     * @param array<int, array<string, mixed>> $history
     */
    private function mockClient(ResponseInterface|\Throwable $result, array &$history): Client
    {
        $stack = HandlerStack::create(new MockHandler([$result]));
        $stack->push(Middleware::history($history));

        return new Client([
            'handler' => $stack,
            'http_errors' => false,
        ]);
    }

    /**
     * @param array<string, mixed> $bodyParams
     */
    private function runDiagnostic(ClientInterface $client, array $bodyParams): Response
    {
        Craft::$app->set('request', new DiagnosticActionRequest($bodyParams));
        $this->installWebResponse();

        $controller = new class($client) extends SettingsController {
            public function __construct(private readonly ClientInterface $client)
            {
                parent::__construct('settings', FormieRestApi::$plugin);
            }

            protected function createHttpClient(): ClientInterface
            {
                return $this->client;
            }
        };

        return $controller->actionRunTest();
    }
}
