<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\formierestapi\models;

use craft\base\Model;
use lindemannrock\base\traits\DateFormatSettingsTrait;
use lindemannrock\base\traits\PluginNameSettingsTrait;
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
    use DateFormatSettingsTrait;
    use PluginNameSettingsTrait;
    use SettingsConfigTrait;
    use SettingsDisplayNameTrait;

    /**
     * @var string The name of the plugin as it appears in the Control Panel menu
     */
    public string $pluginName = 'Formie REST API';

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        return array_merge(
            $this->pluginNameSettingsRules(),
            $this->dateFormatSettingsRules(),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return array_merge(
            $this->pluginNameSettingsLabel(),
            $this->dateFormatSettingsLabels(),
        );
    }

    /**
     * Plugin handle for config file resolution
     */
    protected static function pluginHandle(): string
    {
        return 'formie-rest-api';
    }
}
