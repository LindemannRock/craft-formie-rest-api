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
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsDisplayNameTrait;

/**
 * Formie REST API Settings Model
 *
 * @author    LindemannRock
 * @package   FormieRestApi
 * @since     1.0.0
 */
class Settings extends Model
{
    use SettingsConfigTrait;
    use SettingsDisplayNameTrait;
    /**
     * @var string The public-facing name of the plugin
     */
    public string $pluginName = 'Formie REST API';

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
     * Plugin handle for config file resolution
     */
    protected static function pluginHandle(): string
    {
        return 'formie-rest-api';
    }
}
