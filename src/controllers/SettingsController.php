<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use lindemannrock\base\helpers\CpNavHelper;
use lindemannrock\base\helpers\ExportHelper;
use lindemannrock\base\helpers\SettingsPostHelper;
use lindemannrock\formierestapi\FormieRestApi;
use yii\web\Response;

/**
 * Settings Controller
 *
 * @since 3.4.0
 */
class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // actionIndex resolves its own permission-based redirect (CP nav default route)
        if ($action->id !== 'index') {
            $this->requirePermission('formieRestApi:manageSettings');
        }

        return parent::beforeAction($action);
    }

    /**
     * Settings index — redirect to General, or to the first accessible
     * section for users without settings access (CP nav default route).
     */
    public function actionIndex(): Response
    {
        $user = Craft::$app->getUser();

        if (!$user->checkPermission('formieRestApi:manageSettings')) {
            $settings = FormieRestApi::$plugin->getSettings();
            $sections = FormieRestApi::$plugin->getCpSections($settings, true);
            $route = CpNavHelper::firstAccessibleRoute($user, $settings, $sections);
            if ($route) {
                return $this->redirect($route);
            }

            // No access at all - show 403
            $this->requirePermission('formieRestApi:manageSettings');
        }

        return $this->redirect('formie-rest-api/settings/general');
    }

    /**
     * General settings page.
     */
    public function actionGeneral(): Response
    {
        return $this->renderTemplate('formie-rest-api/settings/general', [
            'settings' => FormieRestApi::$plugin->getSettings(),
        ]);
    }

    /**
     * Interface settings page.
     */
    public function actionInterface(): Response
    {
        return $this->renderTemplate('formie-rest-api/settings/interface', [
            'settings' => FormieRestApi::$plugin->getSettings(),
        ]);
    }

    /**
     * Test page.
     */
    public function actionTest(): Response
    {
        return $this->renderTemplate('formie-rest-api/settings/test', [
            'settings' => FormieRestApi::$plugin->getSettings(),
        ]);
    }

    /**
     * Run a test request against the local REST API.
     */
    public function actionRunTest(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $endpoint = (string) $request->getBodyParam('testEndpoint', 'forms');

        // CP-managed keys are stored hashed, so the server cannot recover their
        // plaintext — the operator pastes the key (and its signing secret, if
        // signing is required) for this test only. Neither value is persisted
        // or logged.
        $apiKey = trim((string) $request->getBodyParam('testPastedKey', ''));
        $pastedSecret = trim((string) $request->getBodyParam('testPastedSecret', ''));
        $signingSecret = $pastedSecret !== '' ? $pastedSecret : null;

        if ($apiKey === '') {
            return $this->asToolError(Craft::t('formie-rest-api', 'Paste an API key to test.'), 400);
        }

        try {
            [$path, $query] = $this->buildEndpoint($endpoint, $request);
        } catch (\InvalidArgumentException $e) {
            return $this->asToolError($e->getMessage(), 422);
        }

        $baseUrl = rtrim(Craft::$app->getSites()->getCurrentSite()->getBaseUrl() ?? '', '/');
        $url = $baseUrl . $path . ($query !== '' ? '?' . $query : '');
        $pathWithQuery = $path . ($query !== '' ? '?' . $query : '');

        $headers = ['X-API-Key' => $apiKey, 'Accept' => 'application/json'];

        // If a signing secret was pasted, sign the request server-side so the
        // test page works against keys that require HMAC.
        if ($signingSecret !== null) {
            $timestamp = (string) time();
            $signatureBase = implode("\n", ['GET', $pathWithQuery, $timestamp, '']);
            $headers['X-Timestamp'] = $timestamp;
            $headers['X-Signature'] = hash_hmac('sha256', $signatureBase, $signingSecret);
        }

        $client = $this->createHttpClient();
        $start = microtime(true);

        try {
            $response = $client->request('GET', $url, [
                'headers' => $headers,
            ]);
            $timeMs = (int) ((microtime(true) - $start) * 1000);

            $responseHeaders = [];
            foreach ($response->getHeaders() as $name => $values) {
                $responseHeaders[$name] = implode(', ', $values);
            }

            $bodyRaw = (string) $response->getBody();
            $bodyDecoded = Json::decodeIfJson($bodyRaw);
            $body = is_array($bodyDecoded)
                ? Json::encode($bodyDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : $bodyRaw;

            return $this->asJson([
                'status' => $response->getStatusCode(),
                'timeMs' => $timeMs,
                'headers' => $responseHeaders,
                'body' => $body,
                'requestOutline' => $this->buildRequestOutline($url, $signingSecret !== null),
            ]);
        } catch (GuzzleException $e) {
            $detail = $this->sanitizeClientError($e->getMessage(), $headers, $signingSecret);

            return $this->asToolError(Craft::t('formie-rest-api', 'API request failed: {error}', [
                'error' => $detail,
            ]), 502);
        } catch (\Throwable) {
            return $this->asToolError(
                Craft::t('formie-rest-api', 'The diagnostic request could not be completed.'),
                500,
            );
        }
    }

    public function actionDownloadPostmanCollection(): Response
    {
        $postmanPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'postman';
        $files = [];

        foreach ([
            'Formie-REST-API.postman_collection.json',
            'Formie-REST-API.postman_environment.json',
            'README.md',
        ] as $filename) {
            $path = $postmanPath . DIRECTORY_SEPARATOR . $filename;
            if (is_file($path)) {
                $content = file_get_contents($path);
                if ($content !== false) {
                    $files[$filename] = $content;
                }
            }
        }

        return ExportHelper::toZip($files, 'formie-rest-api-postman.zip');
    }

    /**
     * Save settings to the plugin's database table (via SettingsPersistenceTrait).
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $plugin = FormieRestApi::$plugin;
        $posted = Craft::$app->getRequest()->getBodyParam('settings', []);
        $settings = $plugin->getSettings();
        $section = $this->validSection((string) Craft::$app->getRequest()->getBodyParam('section', 'general'));

        $sectionAttributes = $this->validationAttributesForSection($section);
        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: is_array($posted) ? $posted : [],
            allowedAttributes: $sectionAttributes,
            shouldSkipAttribute: fn(string $attribute): bool => $settings->isOverriddenByConfig($attribute),
        );
        $attributesToValidate = $result->attributesToValidate;

        if ($result->hasErrors || !$settings->validate($attributesToValidate)) {
            Craft::$app->getSession()->setError(Craft::t('formie-rest-api', 'Couldn\'t save settings.'));
            return $this->renderTemplate("formie-rest-api/settings/{$section}", ['settings' => $settings]);
        }

        if (!$settings->saveToDatabase($attributesToValidate)) {
            Craft::$app->getSession()->setError(Craft::t('formie-rest-api', 'Couldn\'t save settings.'));
            return $this->renderTemplate("formie-rest-api/settings/{$section}", ['settings' => $settings]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('formie-rest-api', 'Settings saved.'));
        return $this->redirectToPostedUrl();
    }

    private function validSection(string $section): string
    {
        $allowed = ['general', 'interface', 'test'];
        return in_array($section, $allowed, true) ? $section : 'general';
    }

    /**
     * @return array<int, string>
     */
    private function validationAttributesForSection(string $section): array
    {
        return match ($section) {
            'general' => [
                'pluginName',
                'logLevel',
            ],
            'interface' => [
                'timeFormat',
                'monthFormat',
                'dateOrder',
                'dateSeparator',
                'showSeconds',
            ],
            default => [],
        };
    }

    /**
     * Build the path + query string for the chosen endpoint.
     *
     * @return array{0: string, 1: string}
     */
    private function buildEndpoint(string $choice, \craft\web\Request $request): array
    {
        $id = $request->getBodyParam('testId', '');
        $handle = $request->getBodyParam('testHandle', '');
        $formHandle = (string) $request->getBodyParam('testFormHandle', '');
        $dateFrom = (string) $request->getBodyParam('testDateFrom', '');
        $dateTo = (string) $request->getBodyParam('testDateTo', '');
        $fields = (string) $request->getBodyParam('testFields', '');
        $limit = (string) $request->getBodyParam('testLimit', '');
        $offset = (string) $request->getBodyParam('testOffset', '');

        $params = [];
        $path = match ($choice) {
            'form-id' => '/api/v1/formie/forms/' . $this->positiveIntegerIdentifier($id),
            'form-handle' => '/api/v1/formie/forms/' . urlencode($this->formHandleIdentifier($handle)),
            'submission-id' => '/api/v1/formie/submissions/' . $this->positiveIntegerIdentifier($id),
            'submissions' => '/api/v1/formie/submissions',
            default => '/api/v1/formie/forms',
        };

        if ($choice === 'submissions') {
            if ($formHandle !== '') {
                $params['formHandle'] = $formHandle;
            }
            if ($dateFrom !== '') {
                $params['dateFrom'] = $dateFrom;
            }
            if ($dateTo !== '') {
                $params['dateTo'] = $dateTo;
            }
            if ($fields !== '') {
                $params['fields'] = $fields;
            }
        }
        if (in_array($choice, ['forms', 'submissions'], true)) {
            if ($limit !== '') {
                $params['limit'] = $limit;
            }
            if ($offset !== '') {
                $params['offset'] = $offset;
            }
        }

        // Sort params alphabetically so the signed query matches what the server
        // receives even when a CDN (e.g. Cloudflare) normalizes query-string order.
        ksort($params);

        return [$path, http_build_query($params)];
    }

    /**
     * Create the package-local HTTP client used by the diagnostic tool.
     *
     * Kept as a narrow seam so supported transport failures can be exercised
     * without opening a real network connection in behavioral tests.
     */
    protected function createHttpClient(): ClientInterface
    {
        return Craft::createGuzzleClient(['http_errors' => false, 'timeout' => 15]);
    }

    private function positiveIntegerIdentifier(mixed $value): string
    {
        if (!is_string($value) && !is_int($value)) {
            throw new \InvalidArgumentException(Craft::t('formie-rest-api', 'Enter a positive integer ID.'));
        }

        $value = trim((string) $value);
        $validated = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($validated === false || (string) $validated !== $value) {
            throw new \InvalidArgumentException(Craft::t('formie-rest-api', 'Enter a positive integer ID.'));
        }

        return (string) $validated;
    }

    private function formHandleIdentifier(mixed $value): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(Craft::t('formie-rest-api', 'Enter a valid form handle.'));
        }

        $value = trim($value);
        if ($value === '' || preg_match('/^[A-Za-z0-9_-]+$/D', $value) !== 1) {
            throw new \InvalidArgumentException(Craft::t('formie-rest-api', 'Enter a valid form handle.'));
        }

        return $value;
    }

    private function buildRequestOutline(string $url, bool $signed): string
    {
        $lines = [
            'GET ' . $url,
            'Accept: application/json',
            'X-API-Key: <API_KEY>',
        ];

        if ($signed) {
            $lines[] = 'X-Timestamp: <TIMESTAMP>';
            $lines[] = 'X-Signature: <SIGNATURE>';
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, string> $headers
     */
    private function sanitizeClientError(string $message, array $headers, ?string $signingSecret): string
    {
        $redactions = array_filter([
            $headers['X-API-Key'] ?? null,
            $headers['X-Timestamp'] ?? null,
            $headers['X-Signature'] ?? null,
            $signingSecret,
        ], static fn(?string $value): bool => is_string($value) && $value !== '');

        $message = str_replace($redactions, '<REDACTED>', $message);
        $message = preg_replace('/\s+/', ' ', trim($message)) ?? '';
        if ($message === '') {
            return Craft::t('formie-rest-api', 'Unknown error');
        }

        return mb_substr($message, 0, 300);
    }

    private function asToolError(string $message, int $statusCode): Response
    {
        return $this->asJson(['error' => $message])->setStatusCode($statusCode);
    }
}
