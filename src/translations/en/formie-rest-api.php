<?php
/**
 * Formie REST API translation file (English)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'Plugin Name',
    'The public-facing name of the plugin' => 'The public-facing name of the plugin',

    // Navigation
    'Settings' => 'Settings',
    'General' => 'General',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Manage settings',

    // Controller messages
    "Couldn't save settings." => "Couldn't save settings.",
    'Settings saved.' => 'Settings saved.',

    // Settings: General
    'General Settings' => 'General Settings',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.',

    // Test page
    'Test API' => 'Test API',
    'Test API Endpoints' => 'Test API Endpoints',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Send a request to the local API using one of the configured keys, and inspect the response.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'API Key',
    'Which configured key to send.' => 'Which configured key to send.',
    'Endpoint' => 'Endpoint',
    'Which REST endpoint to call.' => 'Which REST endpoint to call.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'Numeric form / submission ID.',
    'Form handle' => 'Form handle',
    'Form handle (the slug, not the title).' => 'Form handle (the slug, not the title).',
    'formHandle (optional)' => 'formHandle (optional)',
    'Filter submissions to one form.' => 'Filter submissions to one form.',
    'dateFrom (optional)' => 'dateFrom (optional)',
    'dateTo (optional)' => 'dateTo (optional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Run Test',
    'Result' => 'Result',
    'Status:' => 'Status:',
    'Time:' => 'Time:',
    'Equivalent curl' => 'Equivalent curl',
    'Response headers' => 'Response headers',
    'Response body' => 'Response body',
];
