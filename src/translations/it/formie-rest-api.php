<?php
/**
 * Formie REST API translation file (Italian)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Gestisca le chiavi API, protegga gli endpoint e testi le risposte di dati Formie dall\'area delle impostazioni del plugin.',
    'Open Formie REST API' => 'Apri Formie REST API',
    // Navigation
    'Settings' => 'Impostazioni',
    'General' => 'Generale',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Gestisci impostazioni',

    // Controller messages
    "Couldn't save settings." => 'Impossibile salvare le impostazioni.',
    'Settings saved.' => 'Impostazioni salvate.',

    // Settings: General
    'General Settings' => 'Impostazioni generali',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Questo valore viene sovrascritto dall\'impostazione <code>pluginName</code> in <code>config/formie-rest-api.php</code>.',

    // Settings: Configuration warning
    'COPIED' => 'COPIATO',
    'COPY' => 'COPIA',
    'Configuration Required' => 'Configurazione richiesta',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Generi chiavi separate per ogni ambiente — non copi mai chiavi di produzione su staging o sviluppo.',
    'No API keys configured.' => 'Nessuna chiave API configurata.',
    'Run one of these commands in your terminal:' => 'Esegua uno di questi comandi nel suo terminale:',
    'Standard:' => 'Standard:',
    'The plugin will reject every request until at least one key is set.' => 'Il plugin rifiuterà ogni richiesta finché non sia impostata almeno una chiave.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Questo scriverà {keys} e i corrispondenti segreti di firma nel suo file {file}.',
    'Warning:' => 'Avviso:',
    'error' => 'errore',

    // Test page
    'Test API' => 'Testa API',
    'Test API Endpoints' => 'Testa endpoint API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Invia una richiesta all\'API locale utilizzando una delle chiavi configurate ed esamina la risposta.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Nessuna chiave API configurata. Imposti FORMIE_API_KEY (e facoltativamente FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) nel suo file .env, oppure utilizzi <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'Chiave API',
    'Which configured key to send.' => 'Quale chiave configurata inviare.',
    'Endpoint' => 'Endpoint',
    'Which REST endpoint to call.' => 'Quale endpoint REST chiamare.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'ID numerico del modulo o dell\'invio.',
    'Form handle' => 'Handle del modulo',
    'Form handle (the slug, not the title).' => 'Handle del modulo (lo slug, non il titolo).',
    'formHandle (optional)' => 'formHandle (facoltativo)',
    'Filter submissions to one form.' => 'Filtra gli invii a un solo modulo.',
    'dateFrom (optional)' => 'dateFrom (facoltativo)',
    'dateTo (optional)' => 'dateTo (facoltativo)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Esegui test',
    'Result' => 'Risultato',
    'Status:' => 'Stato:',
    'Time:' => 'Tempo:',
    'Equivalent curl' => 'Comando curl equivalente',
    'Response headers' => 'Intestazioni della risposta',
    'Response body' => 'Corpo della risposta',
];
