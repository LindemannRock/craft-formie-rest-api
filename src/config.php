<?php
/**
 * Formie REST API config.php
 *
 * This file exists only as a template for the Formie REST API settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'formie-rest-api.php'
 * and make your changes there to override default settings.
 *
 * @since 3.3.1
 */

return [
    // Global settings
    '*' => [
        // ========================================
        // GENERAL SETTINGS
        // ========================================

        /**
         * Plugin name shown in the Control Panel.
         */
        // 'pluginName' => 'Formie REST API',

        /**
         * Log level options: 'debug', 'info', 'warning', 'error'
         */
        // 'logLevel' => 'error',


        // ========================================
        // BASE PLUGIN OVERRIDES
        // ========================================
        // These settings override lindemannrock-base defaults for this plugin only.
        // Global defaults: config/lindemannrock-base.php
        // To customize globally: copy to config/lindemannrock-base.php

        /**
         * Date/time formatting overrides
         * Override base plugin date/time display settings for this plugin
         * Defaults: from config/lindemannrock-base.php
         */
        // 'timeFormat' => '24',      // '12' (AM/PM) or '24' (military)
        // 'monthFormat' => 'short',  // 'numeric' (01), 'short' (Jan), 'long' (January)
        // 'dateOrder' => 'dmy',      // 'dmy', 'mdy', 'ymd'
        // 'dateSeparator' => '/',    // '/', '-', '.'
        // 'showSeconds' => false,    // Show seconds in time display
    ],
];
