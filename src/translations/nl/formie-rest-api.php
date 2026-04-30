<?php
/**
 * Formie REST API translation file (Dutch)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Beheer API-sleutels, beveilig endpoints en test Formie-gegevensantwoorden vanuit het instellingengebied van de plugin.',
    'Open Formie REST API' => 'Formie REST API openen',
    'Plugin Name' => 'Pluginnaam',
    'The public-facing name of the plugin' => 'De openbare naam van de plugin',

    // Navigation
    'Settings' => 'Instellingen',
    'General' => 'Algemeen',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Instellingen beheren',

    // Controller messages
    "Couldn't save settings." => 'Kon instellingen niet opslaan.',
    'Settings saved.' => 'Instellingen opgeslagen.',

    // Settings: General
    'General Settings' => 'Algemene instellingen',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Deze waarde wordt overschreven door de instelling <code>pluginName</code> in <code>config/formie-rest-api.php</code>.',

    // Settings: Configuration warning
    'COPIED' => 'GEKOPIEERD',
    'COPY' => 'KOPIËREN',
    'Configuration Required' => 'Configuratie vereist',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Genereer aparte sleutels per omgeving — kopieer nooit productiesleutels naar staging of ontwikkeling.',
    'No API keys configured.' => 'Geen API-sleutels geconfigureerd.',
    'Run one of these commands in your terminal:' => 'Voer een van deze commando\'s uit in uw terminal:',
    'Standard:' => 'Standaard:',
    'The plugin will reject every request until at least one key is set.' => 'De plugin weigert elk verzoek totdat ten minste één sleutel is ingesteld.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Dit schrijft {keys} en bijbehorende ondertekeningsgeheimen naar uw {file}-bestand.',
    'Warning:' => 'Waarschuwing:',
    'error' => 'fout',

    // Test page
    'Test API' => 'API testen',
    'Test API Endpoints' => 'API-endpoints testen',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Stuur een verzoek naar de lokale API met een van de geconfigureerde sleutels en bekijk het antwoord.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Geen API-sleutels geconfigureerd. Stel FORMIE_API_KEY (en optioneel FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in uw .env-bestand in, of gebruik <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'API-sleutel',
    'Which configured key to send.' => 'Welke geconfigureerde sleutel verzonden moet worden.',
    'Endpoint' => 'Endpoint',
    'Which REST endpoint to call.' => 'Welk REST-endpoint aangeroepen moet worden.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'Numerieke formulier- of inzending-ID.',
    'Form handle' => 'Formulierhandle',
    'Form handle (the slug, not the title).' => 'Formulierhandle (de slug, niet de titel).',
    'formHandle (optional)' => 'formHandle (optioneel)',
    'Filter submissions to one form.' => 'Inzendingen tot één formulier beperken.',
    'dateFrom (optional)' => 'dateFrom (optioneel)',
    'dateTo (optional)' => 'dateTo (optioneel)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Test uitvoeren',
    'Result' => 'Resultaat',
    'Status:' => 'Status:',
    'Time:' => 'Duur:',
    'Equivalent curl' => 'Equivalent curl-commando',
    'Response headers' => 'Antwoordheaders',
    'Response body' => 'Antwoordbody',
];
