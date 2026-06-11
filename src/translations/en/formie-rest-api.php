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
    'Interface' => 'Interface',
    'Logs' => 'Logs',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Manage settings',
    'Manage API keys' => 'Manage API keys',
    'Create API keys' => 'Create API keys',
    'Edit API keys' => 'Edit API keys',
    'Revoke API keys' => 'Revoke API keys',
    'View system logs' => 'View system logs',
    'Download system logs' => 'Download system logs',

    // Common
    'Name' => 'Name',
    'Status' => 'Status',
    'Actions' => 'Actions',
    'All' => 'All',
    'Enable' => 'Enable',
    'Disable' => 'Disable',
    'Enabled' => 'Enabled',
    'Disabled' => 'Disabled',
    'Edit' => 'Edit',
    'Save' => 'Save',
    'Save and continue editing' => 'Save and continue editing',
    'Set status' => 'Set status',
    'Never' => 'Never',
    'Created at' => 'Created at',
    'Updated at' => 'Updated at',

    // Controller messages
    "Couldn't save settings." => "Couldn't save settings.",
    'Settings saved.' => 'Settings saved.',
    'Selected API key is not configured.' => 'Selected API key is not configured.',
    'API key created' => 'API key created',
    'API key saved' => 'API key saved',
    'API key revoked' => 'API key revoked',
    'Couldn’t save API key' => 'Couldn’t save API key',
    'Couldn’t revoke API key' => 'Couldn’t revoke API key',
    'API key not found' => 'API key not found',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 API key revoked} other{# API keys revoked}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 API key enabled} other{# API keys enabled}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 API key disabled} other{# API keys disabled}}',
    'Couldn’t enable API keys' => 'Couldn’t enable API keys',
    'Couldn’t disable API keys' => 'Couldn’t disable API keys',
    'Couldn’t revoke API keys' => 'Couldn’t revoke API keys',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Enabled keys must allow all forms or at least one specific form.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'General Settings',

    // Settings: Interface
    'Interface Settings' => 'Interface Settings',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'No API keys have been created yet. Create a key per consumer to control access to the REST API.',

    // Index page
    'Allowed forms' => 'Allowed forms',
    'Signing' => 'Signing',
    'Expires' => 'Expires',
    'Last used' => 'Last used',
    'Expired' => 'Expired',
    'No API keys yet.' => 'No API keys yet.',
    'Search API keys...' => 'Search API keys...',
    'API key' => 'API key',
    'API keys' => 'API keys',
    'All Forms' => 'All Forms',
    'form' => 'form',
    'forms' => 'forms',
    'No forms allowed — this key cannot be used until you add some.' => 'No forms allowed — this key cannot be used until you add some.',
    'Revoke' => 'Revoke',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.',
    'Prefix' => 'Prefix',
    'None' => 'None',

    // Edit page
    'New API Key' => 'New API Key',
    'Edit API Key' => 'Edit API Key',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.',
    'All forms (current and future)' => 'All forms (current and future)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.',
    'Specific forms' => 'Specific forms',
    'Tick each form this key can read.' => 'Tick each form this key can read.',
    'No forms exist yet. Create a form before this key can be useful.' => 'No forms exist yet. Create a form before this key can be useful.',
    'IP whitelist' => 'IP whitelist',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.',
    'Require signing' => 'Require signing',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.',
    'Read submissions' => 'Read submissions',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'When off, this key is limited to the forms endpoints and cannot read any submission data.',
    'Rate limit' => 'Rate limit',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Cap the request rate in requests per hour. Leave empty for the default (100/hour).',
    'Valid until' => 'Valid until',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Optional expiry datetime. Leave empty for no expiry.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.',
    'Copy this API key now — it will never be shown again.' => 'Copy this API key now — it will never be shown again.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} stores only a hash. If you lose this value you will need to create a new key.',
    'Copy this signing secret now — it will never be shown again.' => 'Copy this signing secret now — it will never be shown again.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.',

    // Test page
    'Test API' => 'Test API',
    'Test API Endpoints' => 'Test API Endpoints',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Send a request to the local API using one of the configured keys, and inspect the response.',
    'Developer resources' => 'Developer resources',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Download the Postman collection and environment to test the Formie REST API outside Craft.',
    'Download Postman collection' => 'Download Postman collection',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'API Key',
    'Which configured key to send.' => 'Which configured key to send.',
    'Pasted key' => 'Pasted key',
    'Paste an API key to test.' => 'Paste an API key to test.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Paste the full key (fra_...). Used for this test only — never stored.',
    'Signing secret' => 'Signing secret',
    'Leave empty if the key does not require signing.' => 'Leave empty if the key does not require signing.',
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
    'fields (optional)' => 'fields (optional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Run Test',
    'Result' => 'Result',
    'Status:' => 'Status:',
    'Time:' => 'Time:',
    'Equivalent curl' => 'Equivalent curl',
    'Response headers' => 'Response headers',
    'Response body' => 'Response body',
    'Running...' => 'Running...',
    'Error:' => 'Error:',
    'Unknown error' => 'Unknown error',
];
