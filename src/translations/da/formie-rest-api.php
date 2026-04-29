<?php
/**
 * Formie REST API translation file (Danish)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'Plugin-navn',
    'The public-facing name of the plugin' => 'Pluginnets offentlige navn',

    // Navigation
    'Settings' => 'Indstillinger',
    'General' => 'Generelt',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Administrer indstillinger',

    // Controller messages
    "Couldn't save settings." => 'Indstillingerne kunne ikke gemmes.',
    'Settings saved.' => 'Indstillinger gemt.',

    // Settings: General
    'General Settings' => 'Generelle indstillinger',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Denne værdi tilsidesættes af indstillingen <code>pluginName</code> i <code>config/formie-rest-api.php</code>.',

    // Test page
    'Test API' => 'Test API',
    'Test API Endpoints' => 'Test API-endpoints',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Send en anmodning til den lokale API med en af de konfigurerede nøgler, og se svaret.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Ingen API-nøgler konfigureret. Angiv FORMIE_API_KEY (og eventuelt FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) i din .env-fil, eller brug <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'API-nøgle',
    'Which configured key to send.' => 'Hvilken konfigureret nøgle der skal sendes.',
    'Endpoint' => 'Endpoint',
    'Which REST endpoint to call.' => 'Hvilket REST-endpoint der skal kaldes.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'Numerisk formular- eller indsendelses-ID.',
    'Form handle' => 'Formularhandle',
    'Form handle (the slug, not the title).' => 'Formularhandle (slug, ikke titlen).',
    'formHandle (optional)' => 'formHandle (valgfri)',
    'Filter submissions to one form.' => 'Begræns indsendelser til én formular.',
    'dateFrom (optional)' => 'dateFrom (valgfri)',
    'dateTo (optional)' => 'dateTo (valgfri)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Kør test',
    'Result' => 'Resultat',
    'Status:' => 'Status:',
    'Time:' => 'Tid:',
    'Equivalent curl' => 'Tilsvarende curl-kommando',
    'Response headers' => 'Svarheaders',
    'Response body' => 'Svarbody',
];
