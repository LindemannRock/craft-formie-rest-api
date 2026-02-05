<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * REST and GraphQL API for Formie - Provides API endpoints for accessing Formie forms and submissions
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\formierestapi;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\formierestapi\models\Settings;
use lindemannrock\formierestapi\services\ApiKeyService;
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
        PluginHelper::bootstrap($this, 'formieRestApiHelper');

        // Set the alias for this plugin
        Craft::setAlias('@lindemannrock/formierestapi', __DIR__);
        
        // Create class alias for backward compatibility
        class_alias(
            \lindemannrock\formierestapi\services\ApiKeyService::class,
            'lindemannrock\modules\formierestapi\services\ApiKeyService'
        );
        class_alias(
            \lindemannrock\formierestapi\services\SecurityService::class,
            'lindemannrock\modules\formierestapi\services\SecurityService'
        );
        
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
                
                // Test endpoints (can be disabled in production)
                $event->rules['api/test/formie/forms'] = 'formie-rest-api/api-test/forms';
                $event->rules['api/test/formie/submissions'] = 'formie-rest-api/api-test/submissions';
                $event->rules['api/test/formie/auth'] = 'formie-rest-api/api-test/test-auth';
                
                // GraphQL test endpoints
                $event->rules['api/test/graphql/info'] = 'formie-rest-api/graphql-test/info';
                $event->rules['api/test/graphql/examples'] = 'formie-rest-api/graphql-test/examples';
                $event->rules['api/test/graphql/query'] = 'formie-rest-api/graphql-test/query';
                $event->rules['api/test/graphql/compare'] = 'formie-rest-api/graphql-test/compare';
                $event->rules['api/test/graphql/schema'] = 'formie-rest-api/graphql-test/schema';
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
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'formie-rest-api/settings',
            [
                'settings' => $this->getSettings(),
                'plugin' => $this,
            ]
        );
    }
}
