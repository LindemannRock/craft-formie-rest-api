<?php
/**
 * Formie REST API translation file (Norwegian)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Administrer API-nøkler, sikre endepunkter og test Formie-datasvar fra pluginets innstillingsområde.',
    'Open Formie REST API' => 'Åpne Formie REST API',
    // Navigation
    'Settings' => 'Innstillinger',
    'Plugins' => 'Plugins',
    'General' => 'Generelt',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Administrer innstillinger',

    // Controller messages
    "Couldn't save settings." => 'Innstillingene kunne ikke lagres.',
    'Settings saved.' => 'Innstillinger lagret.',

    // Settings: General
    'General Settings' => 'Generelle innstillinger',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Denne verdien overstyres av innstillingen <code>pluginName</code> i <code>config/formie-rest-api.php</code>.',

    // Settings: Configuration warning
    'COPIED' => 'KOPIERT',
    'COPY' => 'KOPIER',
    'Configuration Required' => 'Konfigurasjon kreves',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Generer separate nøkler per miljø — kopier aldri produksjonsnøkler til staging eller utvikling.',
    'No API keys configured.' => 'Ingen API-nøkler konfigurert.',
    'Run one of these commands in your terminal:' => 'Kjør en av disse kommandoene i terminalen din:',
    'Standard:' => 'Standard:',
    'The plugin will reject every request until at least one key is set.' => 'Pluginet avviser alle forespørsler til minst én nøkkel er angitt.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Dette skriver {keys} og tilhørende signeringshemmeligheter til {file}-filen din.',
    'Warning:' => 'Advarsel:',
    'error' => 'feil',

    // Test page
    'Test API' => 'Test API',
    'Test API Endpoints' => 'Test API-endepunkter',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Send en forespørsel til det lokale API-et med en av de konfigurerte nøklene, og se på svaret.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Ingen API-nøkler konfigurert. Angi FORMIE_API_KEY (og eventuelt FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) i .env-filen din, eller bruk <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'API-nøkkel',
    'Which configured key to send.' => 'Hvilken konfigurert nøkkel som skal sendes.',
    'Endpoint' => 'Endepunkt',
    'Which REST endpoint to call.' => 'Hvilket REST-endepunkt som skal kalles.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'Numerisk skjema- eller innsendings-ID.',
    'Form handle' => 'Skjemahandle',
    'Form handle (the slug, not the title).' => 'Skjemahandle (slug, ikke tittelen).',
    'formHandle (optional)' => 'formHandle (valgfri)',
    'Filter submissions to one form.' => 'Begrens innsendinger til ett skjema.',
    'dateFrom (optional)' => 'dateFrom (valgfri)',
    'dateTo (optional)' => 'dateTo (valgfri)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Kjør test',
    'Result' => 'Resultat',
    'Status:' => 'Status:',
    'Time:' => 'Tid:',
    'Equivalent curl' => 'Tilsvarende curl-kommando',
    'Response headers' => 'Svar-headere',
    'Response body' => 'Svar-body',
];
