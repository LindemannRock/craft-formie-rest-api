<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * REST API for Formie - REST endpoints for accessing Formie forms and submissions
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025-2026 LindemannRock
 */

namespace lindemannrock\formierestapi;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use lindemannrock\base\helpers\CpNavHelper;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\formierestapi\models\Settings;
use lindemannrock\formierestapi\services\ApiKeyService;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\services\SecurityService;
use lindemannrock\logginglibrary\LoggingLibrary;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use yii\base\Event;

/**
 * Formie REST API Plugin
 *
 * @author    LindemannRock
 * @package   FormieRestApi
 * @since     1.0.0
 *
 * @property-read Settings $settings
 * @property-read ApiKeyService $apiKey
 * @property-read SecurityService $security
 * @property-read FormieTransformerService $transformer
 * @method Settings getSettings()
 */
class FormieRestApi extends Plugin
{
    use LoggingTrait;

    /**
     * @var FormieRestApi|null Singleton plugin instance
     */
    public static ?FormieRestApi $plugin = null;

    /**
     * @var string Plugin schema version for migrations
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool Whether the plugin has its own section in the CP nav
     */
    public bool $hasCpSection = true;

    /**
     * @var bool Whether the plugin exposes a control panel settings page
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool Whether the settings page is reachable when admin changes are disabled.
     *
     * Must be set explicitly because we override getSettingsResponse(): Craft only
     * auto-enables this for plugins using the default settings response, so without
     * it the plugin is hidden from Settings when allowAdminChanges is off.
     */
    public bool $hasReadOnlyCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Bootstrap the base plugin helper
        PluginHelper::bootstrap($this, 'formieRestApiHelper', ['formieRestApi:viewSystemLogs'], ['formieRestApi:downloadSystemLogs'], [
            'installExperience' => [
                'headline' => Craft::t('formie-rest-api', 'Formie REST API'),
                'body' => Craft::t('formie-rest-api', 'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.'),
                'ctaLabel' => Craft::t('formie-rest-api', 'Open Formie REST API'),
                'ctaUrl' => 'formie-rest-api/settings',
                'redirectUri' => 'formie-rest-api/settings',
                'confettiPreset' => 'surprise',
            ],
        ]);

        // Set the alias for this plugin
        Craft::setAlias('@lindemannrock/formierestapi', __DIR__);

        // Set the controllerNamespace based on request type
        if (Craft::$app instanceof \craft\console\Application) {
            $this->controllerNamespace = 'lindemannrock\formierestapi\console\controllers';
        } else {
            $this->controllerNamespace = 'lindemannrock\formierestapi\controllers';
        }
        
        // Register services
        $this->setComponents([
            'apiKey' => ApiKeyService::class,
            'security' => SecurityService::class,
            'transformer' => FormieTransformerService::class,
        ]);

        // Register API routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                // Production API routes
                $event->rules['api/v1/formie/forms'] = 'formie-rest-api/api/forms';
                $event->rules['api/v1/formie/forms/<formId:\d+>'] = 'formie-rest-api/api/form-detail';
                $event->rules['api/v1/formie/forms/<handle:[\w\-]+>'] = 'formie-rest-api/api/form-by-handle';
                $event->rules['api/v1/formie/submissions'] = 'formie-rest-api/api/submissions';
                $event->rules['api/v1/formie/submissions/<submissionId:\d+>'] = 'formie-rest-api/api/submission-detail';

                // Test endpoints — registered only when devMode is on.
                if (Craft::$app->getConfig()->getGeneral()->devMode) {
                    $event->rules['api/test/formie/forms'] = 'formie-rest-api/api-test/forms';
                    $event->rules['api/test/formie/submissions'] = 'formie-rest-api/api-test/submissions';
                    $event->rules['api/test/formie/auth'] = 'formie-rest-api/api-test/test-auth';
                }
            }
        );

        // Register CP URL rules for the multi-page settings UI
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['formie-rest-api'] = 'formie-rest-api/api-keys/index';
                $event->rules['formie-rest-api/api-keys'] = 'formie-rest-api/api-keys/index';
                $event->rules['formie-rest-api/api-keys/create'] = 'formie-rest-api/api-keys/edit';
                $event->rules['formie-rest-api/api-keys/edit/<keyId:\d+>'] = 'formie-rest-api/api-keys/edit';
                $event->rules['formie-rest-api/api-keys/delete/<keyId:\d+>'] = 'formie-rest-api/api-keys/delete';
                $event->rules['formie-rest-api/api-keys/bulk-enable'] = 'formie-rest-api/api-keys/bulk-enable';
                $event->rules['formie-rest-api/api-keys/bulk-disable'] = 'formie-rest-api/api-keys/bulk-disable';
                $event->rules['formie-rest-api/api-keys/bulk-delete'] = 'formie-rest-api/api-keys/bulk-delete';
                $event->rules['formie-rest-api/settings'] = 'formie-rest-api/settings/index';
                $event->rules['formie-rest-api/settings/general'] = 'formie-rest-api/settings/general';
                $event->rules['formie-rest-api/settings/interface'] = 'formie-rest-api/settings/interface';
                $event->rules['formie-rest-api/settings/test'] = 'formie-rest-api/settings/test';
                $event->rules['formie-rest-api/settings/run-test'] = 'formie-rest-api/settings/run-test';
                $event->rules['formie-rest-api/settings/download-postman-collection'] = 'formie-rest-api/settings/download-postman-collection';
                $event->rules['formie-rest-api/settings/save'] = 'formie-rest-api/settings/save';
            }
        );

        // Register permissions
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => $this->getSettings()->getFullName(),
                    'permissions' => [
                        'formieRestApi:manageSettings' => [
                            'label' => Craft::t('formie-rest-api', 'Manage settings'),
                        ],
                        // API Keys - grouped (parent grants page access + view, destructive actions nested)
                        'formieRestApi:manageApiKeys' => [
                            'label' => Craft::t('formie-rest-api', 'Manage API keys'),
                            'nested' => [
                                'formieRestApi:createApiKeys' => [
                                    'label' => Craft::t('formie-rest-api', 'Create API keys'),
                                ],
                                'formieRestApi:editApiKeys' => [
                                    'label' => Craft::t('formie-rest-api', 'Edit API keys'),
                                ],
                                'formieRestApi:revokeApiKeys' => [
                                    'label' => Craft::t('formie-rest-api', 'Revoke API keys'),
                                ],
                            ],
                        ],
                        'formieRestApi:viewSystemLogs' => [
                            'label' => Craft::t('formie-rest-api', 'View system logs'),
                            'nested' => [
                                'formieRestApi:downloadSystemLogs' => [
                                    'label' => Craft::t('formie-rest-api', 'Download system logs'),
                                ],
                            ],
                        ],
                    ],
                ];
            }
        );
        
        // Set the plugin name from settings
        $settings = $this->getSettings();
        if (!empty($settings->pluginName)) {
            $this->name = $settings->pluginName;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $user = Craft::$app->getUser();

        if ($item) {
            $settings = $this->getSettings();

            $item['label'] = $settings->getFullName();

            $sections = $this->getCpSections($settings);
            $item['subnav'] = CpNavHelper::buildSubnav($user, $settings, $sections);

            // System Logs (using logging library)
            if (PluginHelper::isPluginEnabled('logging-library')) {
                $item = LoggingLibrary::addLogsNav($item, $this->handle, [
                    'formieRestApi:viewSystemLogs',
                ]);
            }

            // Hide from nav if no accessible subnav items
            if (empty($item['subnav'])) {
                return null;
            }
        }

        return $item;
    }

    /**
     * Get CP sections for nav + default route resolution
     *
     * @since 3.10.0
     */
    public function getCpSections(Settings $settings, bool $includeLogs = false): array
    {
        $sections = [];

        $sections[] = [
            'key' => 'api-keys',
            'label' => Craft::t('formie-rest-api', 'API Keys'),
            'url' => 'formie-rest-api',
            'permissionsAll' => ['formieRestApi:manageApiKeys'],
        ];

        if ($includeLogs) {
            $sections[] = [
                'key' => 'logs',
                'label' => Craft::t('formie-rest-api', 'Logs'),
                'url' => 'formie-rest-api/logs',
                'permissionsAll' => ['formieRestApi:viewSystemLogs'],
                'when' => fn() => PluginHelper::isPluginEnabled('logging-library'),
            ];
        }

        $sections[] = [
            'key' => 'settings',
            'label' => Craft::t('formie-rest-api', 'Settings'),
            'url' => 'formie-rest-api/settings',
            'permissionsAll' => ['formieRestApi:manageSettings'],
        ];

        return $sections;
    }

    /**
     * @inheritdoc
     */
    public function setSettings(array|Model $settings): void
    {
        // No-op: settings come from loadFromDatabase() in createSettingsModel(),
        // not from project config.
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        try {
            return Settings::loadFromDatabase();
        } catch (\Throwable $e) {
            $this->logError('Could not load settings from database', [
                'error' => $e->getMessage(),
            ]);
            return new Settings();
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect('formie-rest-api/settings');
    }

    /**
     * @inheritdoc
     */
    public function getReadOnlySettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect('formie-rest-api/settings');
    }
}
