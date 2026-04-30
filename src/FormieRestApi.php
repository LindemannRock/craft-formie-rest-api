<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * REST API for Formie - REST endpoints for accessing Formie forms and submissions
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\formierestapi;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\formierestapi\models\Settings;
use lindemannrock\formierestapi\services\ApiKeyService;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\services\SecurityService;
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
    /**
     * @var FormieRestApi|null Singleton plugin instance
     */
    public static ?FormieRestApi $plugin = null;

    /**
     * @var string Plugin schema version for migrations
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool Whether the plugin exposes a control panel settings page
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Bootstrap the base plugin helper
        PluginHelper::bootstrap($this, 'formieRestApiHelper', [], [], [
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
                $event->rules['formie-rest-api/settings'] = 'formie-rest-api/settings/index';
                $event->rules['formie-rest-api/settings/general'] = 'formie-rest-api/settings/general';
                $event->rules['formie-rest-api/settings/test'] = 'formie-rest-api/settings/test';
                $event->rules['formie-rest-api/settings/run-test'] = 'formie-rest-api/settings/run-test';
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
                    ],
                ];
            }
        );
        
        // Set the plugin name from settings
        $settings = $this->getSettings();
        if (!empty($settings->pluginName)) {
            $this->name = $settings->pluginName;
        }

        Craft::info(
            'Formie REST API plugin loaded',
            __METHOD__
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
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
