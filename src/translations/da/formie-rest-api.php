<?php
/**
 * Formie REST API translation file (Danish)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Administrer API-nøgler, sikr endpoints, og test Formie-datasvar fra pluginets indstillingsområde.',
    'Open Formie REST API' => 'Åbn Formie REST API',
    // Navigation
    'Settings' => 'Indstillinger',
    'Plugins' => 'Plugins',
    'General' => 'Generelt',
    'Interface' => 'Brugerflade',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Administrer indstillinger',

    // Controller messages
    "Couldn't save settings." => 'Indstillingerne kunne ikke gemmes.',
    'Settings saved.' => 'Indstillinger gemt.',
    'Selected API key is not configured.' => 'Den valgte API-nøgle er ikke konfigureret.',

    // Settings: General
    'General Settings' => 'Generelle indstillinger',

    // Settings: Interface
    'Interface Settings' => 'Brugerflade-indstillinger',

    // Settings: Configuration warning
    'COPIED' => 'KOPIERET',
    'COPY' => 'KOPIER',
    'Configuration Required' => 'Konfiguration kræves',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Generer separate nøgler pr. miljø — kopier aldrig produktionsnøgler til staging eller udvikling.',
    'No API keys configured.' => 'Ingen API-nøgler konfigureret.',
    'Run one of these commands in your terminal:' => 'Kør en af disse kommandoer i din terminal:',
    'Standard:' => 'Standard:',
    'The plugin will reject every request until at least one key is set.' => 'Pluginet afviser alle anmodninger, indtil mindst én nøgle er angivet.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Dette skriver {keys} og matchende signeringshemmeligheder til din {file}-fil.',
    'Warning:' => 'Advarsel:',
    'error' => 'fejl',

    // Test page
    'Test API' => 'Test API',
    'Test API Endpoints' => 'Test API-endpoints',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Send en anmodning til det lokale API med en af de konfigurerede nøgler, og gennemgå svaret.',
    'Developer resources' => 'Udviklerressourcer',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Download Postman-samlingen og -miljøet for at teste Formie REST API uden for Craft.',
    'Download Postman collection' => 'Download Postman-samling',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'Ingen API-nøgler konfigureret. Angiv FORMIE_API_KEY (og eventuelt FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) i din .env-fil, eller kør <code>php craft formie-rest-api/security/generate-key</code> (med DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
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
    'Running…' => 'Kører…',
    'Error:' => 'Fejl:',
    'Unknown error' => 'Ukendt fejl',
];
