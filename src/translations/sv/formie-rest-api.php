<?php
/**
 * Formie REST API translation file (Swedish)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Hantera API-nycklar, säkra slutpunkter och testa Formie-datasvar från pluginets inställningsområde.',
    'Open Formie REST API' => 'Öppna Formie REST API',
    // Navigation
    'Settings' => 'Inställningar',
    'Plugins' => 'Plugins',
    'General' => 'Allmänt',
    'Interface' => 'Gränssnitt',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Hantera inställningar',

    // Controller messages
    "Couldn't save settings." => 'Det gick inte att spara inställningarna.',
    'Settings saved.' => 'Inställningarna har sparats.',
    'Selected API key is not configured.' => 'Den valda API-nyckeln är inte konfigurerad.',

    // Settings: General
    'General Settings' => 'Allmänna inställningar',

    // Settings: Interface
    'Interface Settings' => 'Gränssnittsinställningar',

    // Settings: Configuration warning
    'COPIED' => 'KOPIERAD',
    'COPY' => 'KOPIERA',
    'Configuration Required' => 'Konfiguration krävs',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Generera separata nycklar per miljö — kopiera aldrig produktionsnycklar till staging eller utveckling.',
    'No API keys configured.' => 'Inga API-nycklar konfigurerade.',
    'Run one of these commands in your terminal:' => 'Kör ett av dessa kommandon i din terminal:',
    'Standard:' => 'Standard:',
    'The plugin will reject every request until at least one key is set.' => 'Pluginet avvisar alla begäranden tills minst en nyckel är angiven.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Detta skriver {keys} och matchande signeringshemligheter till din {file}-fil.',
    'Warning:' => 'Varning:',
    'error' => 'fel',

    // Test page
    'Test API' => 'Testa API',
    'Test API Endpoints' => 'Testa API-slutpunkter',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Skicka en begäran till den lokala API:n med en av de konfigurerade nycklarna och granska svaret.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'Inga API-nycklar konfigurerade. Ange FORMIE_API_KEY (och eventuellt FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) i din .env-fil, eller kör <code>php craft formie-rest-api/security/generate-key</code> (med DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'API-nyckel',
    'Which configured key to send.' => 'Vilken konfigurerad nyckel som ska skickas.',
    'Endpoint' => 'Slutpunkt',
    'Which REST endpoint to call.' => 'Vilken REST-slutpunkt som ska anropas.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'Numeriskt formulär- eller inlämnings-ID.',
    'Form handle' => 'Formulärhandtag',
    'Form handle (the slug, not the title).' => 'Formulärhandtag (slug, inte titeln).',
    'formHandle (optional)' => 'formHandle (valfri)',
    'Filter submissions to one form.' => 'Begränsa inlämningar till ett formulär.',
    'dateFrom (optional)' => 'dateFrom (valfri)',
    'dateTo (optional)' => 'dateTo (valfri)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Kör test',
    'Result' => 'Resultat',
    'Status:' => 'Status:',
    'Time:' => 'Tid:',
    'Equivalent curl' => 'Motsvarande curl-kommando',
    'Response headers' => 'Svarshuvuden',
    'Response body' => 'Svarskropp',
    'Running…' => 'Kör…',
    'Error:' => 'Fel:',
    'Unknown error' => 'Okänt fel',
];
