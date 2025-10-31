<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\formierestapi\models;

use Craft;
use craft\base\Model;

/**
 * Formie REST API Settings Model
 *
 * @author    LindemannRock
 * @package   FormieRestApi
 * @since     1.0.0
 */
class Settings extends Model
{
    /**
     * @var string|null The public-facing name of the plugin
     */
    public ?string $pluginName = 'Formie REST API';

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        return [
            [['pluginName'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'pluginName' => Craft::t('formie-rest-api', 'Plugin Name'),
        ];
    }

    /**
     * Check if a setting is overridden in config file
     *
     * @param string $setting
     * @return bool
     */
    public function isOverriddenByConfig(string $setting): bool
    {
        $configFileSettings = Craft::$app->getConfig()->getConfigFromFile('formie-rest-api');
        return isset($configFileSettings[$setting]);
    }
}
