<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\controllers;

use Craft;
use craft\helpers\App;
use craft\helpers\Json;
use craft\web\Controller;
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
        $this->requirePermission('formieRestApi:manageSettings');

        return parent::beforeAction($action);
    }

    /**
     * Settings index — redirect to General.
     */
    public function actionIndex(): Response
    {
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
     * Test page.
     */
    public function actionTest(): Response
    {
        return $this->renderTemplate('formie-rest-api/settings/test', [
            'settings' => FormieRestApi::$plugin->getSettings(),
            'availableKeys' => $this->getAvailableKeys(),
            'keyOptions' => $this->getKeyOptions(),
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
        $keyChoice = (string) $request->getBodyParam('testKey', 'primary');
        $endpoint = (string) $request->getBodyParam('testEndpoint', 'forms');

        $apiKey = $this->resolveKey($keyChoice);
        if ($apiKey === null) {
            return $this->asJson(['error' => 'Selected API key is not configured.']);
        }

        [$path, $query] = $this->buildEndpoint($endpoint, $request);
        $baseUrl = rtrim(Craft::$app->getSites()->getCurrentSite()->getBaseUrl() ?? '', '/');
        $url = $baseUrl . $path . ($query !== '' ? '?' . $query : '');

        $client = Craft::createGuzzleClient(['http_errors' => false, 'timeout' => 15]);
        $start = microtime(true);

        try {
            $response = $client->request('GET', $url, [
                'headers' => ['X-API-Key' => $apiKey, 'Accept' => 'application/json'],
            ]);
            $timeMs = (int) ((microtime(true) - $start) * 1000);

            $headers = [];
            foreach ($response->getHeaders() as $name => $values) {
                $headers[$name] = implode(', ', $values);
            }

            $bodyRaw = (string) $response->getBody();
            $bodyDecoded = Json::decodeIfJson($bodyRaw);
            $body = is_array($bodyDecoded)
                ? Json::encode($bodyDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : $bodyRaw;

            return $this->asJson([
                'status' => $response->getStatusCode(),
                'timeMs' => $timeMs,
                'headers' => $headers,
                'body' => $body,
                'curl' => $this->buildCurl($url, $apiKey),
            ]);
        } catch (\Throwable $e) {
            return $this->asJson(['error' => $e->getMessage()]);
        }
    }

    /**
     * Save settings (project config — Craft handles persistence).
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $plugin = FormieRestApi::$plugin;
        $posted = Craft::$app->getRequest()->getBodyParam('settings', []);
        $settings = $plugin->getSettings();

        foreach ($posted as $key => $value) {
            if (property_exists($settings, $key) && !$settings->isOverriddenByConfig($key)) {
                $settings->$key = $value;
            }
        }

        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray())) {
            Craft::$app->getSession()->setError(Craft::t('formie-rest-api', 'Couldn\'t save settings.'));
            return $this->renderTemplate('formie-rest-api/settings/general', ['settings' => $settings]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('formie-rest-api', 'Settings saved.'));
        return $this->redirectToPostedUrl();
    }

    /**
     * Available key types where the env var is set.
     */
    private function getAvailableKeys(): array
    {
        $available = [];
        if (App::env('FORMIE_API_KEY')) {
            $available['primary'] = 'Primary (FORMIE_API_KEY)';
        }
        if (App::env('FORMIE_API_KEY_LIMITED')) {
            $available['limited'] = 'Limited (FORMIE_API_KEY_LIMITED)';
        }
        if (App::env('FORMIE_API_KEY_TEST') && Craft::$app->getConfig()->getGeneral()->devMode) {
            $available['test'] = 'Test (FORMIE_API_KEY_TEST)';
        }
        return $available;
    }

    /**
     * Build the dropdown options array for the key picker.
     */
    private function getKeyOptions(): array
    {
        $options = [];
        foreach ($this->getAvailableKeys() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }

    /**
     * Resolve a key choice to the underlying secret.
     */
    private function resolveKey(string $choice): ?string
    {
        $envVar = match ($choice) {
            'limited' => 'FORMIE_API_KEY_LIMITED',
            'test' => 'FORMIE_API_KEY_TEST',
            default => 'FORMIE_API_KEY',
        };

        $value = App::env($envVar);
        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Build the path + query string for the chosen endpoint.
     *
     * @return array{0: string, 1: string}
     */
    private function buildEndpoint(string $choice, \craft\web\Request $request): array
    {
        $id = (string) $request->getBodyParam('testId', '');
        $handle = (string) $request->getBodyParam('testHandle', '');
        $formHandle = (string) $request->getBodyParam('testFormHandle', '');
        $dateFrom = (string) $request->getBodyParam('testDateFrom', '');
        $dateTo = (string) $request->getBodyParam('testDateTo', '');
        $limit = (string) $request->getBodyParam('testLimit', '');
        $offset = (string) $request->getBodyParam('testOffset', '');

        $params = [];
        $path = match ($choice) {
            'form-id' => '/api/v1/formie/forms/' . urlencode($id),
            'form-handle' => '/api/v1/formie/forms/' . urlencode($handle),
            'submission-id' => '/api/v1/formie/submissions/' . urlencode($id),
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
        }
        if (in_array($choice, ['forms', 'submissions'], true)) {
            if ($limit !== '') {
                $params['limit'] = $limit;
            }
            if ($offset !== '') {
                $params['offset'] = $offset;
            }
        }

        return [$path, http_build_query($params)];
    }

    /**
     * Build a copy-pasteable curl command (key partially masked).
     */
    private function buildCurl(string $url, string $apiKey): string
    {
        $masked = substr($apiKey, 0, 10) . '...';
        return sprintf('curl -i -H "X-API-Key: %s" "%s"', $masked, $url);
    }
}
