<?php
/**
 * Formie REST API translation file (English)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.',
    'Open Formie REST API' => 'Open Formie REST API',
    // Navigation
    'Settings' => 'Settings',
    'Plugins' => 'Plugins',
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

    // Settings: Configuration warning
    'COPIED' => 'COPIED',
    'COPY' => 'COPY',
    'Configuration Required' => 'Configuration Required',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Generate separate keys per environment — never copy production keys to staging or development.',
    'No API keys configured.' => 'No API keys configured.',
    'Run one of these commands in your terminal:' => 'Run one of these commands in your terminal:',
    'Standard:' => 'Standard:',
    'The plugin will reject every request until at least one key is set.' => 'The plugin will reject every request until at least one key is set.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'This will write {keys} and matching signing secrets to your {file} file.',
    'Warning:' => 'Warning:',
    'error' => 'error',

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
