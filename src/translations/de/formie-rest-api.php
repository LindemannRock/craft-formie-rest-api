<?php
/**
 * Formie REST API translation file (German)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'Plugin-Name',
    'The public-facing name of the plugin' => 'Der öffentlich sichtbare Name des Plugins',

    // Navigation
    'Settings' => 'Einstellungen',
    'General' => 'Allgemein',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Einstellungen verwalten',

    // Controller messages
    "Couldn't save settings." => 'Einstellungen konnten nicht gespeichert werden.',
    'Settings saved.' => 'Einstellungen gespeichert.',

    // Settings: General
    'General Settings' => 'Allgemeine Einstellungen',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Dieser Wert wird durch die Einstellung <code>pluginName</code> in <code>config/formie-rest-api.php</code> überschrieben.',

    // Test page
    'Test API' => 'API testen',
    'Test API Endpoints' => 'API-Endpunkte testen',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Sendet eine Anfrage an die lokale API mit einem der konfigurierten Schlüssel und zeigt die Antwort an.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Keine API-Schlüssel konfiguriert. Setzen Sie FORMIE_API_KEY (und optional FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in Ihrer .env-Datei oder verwenden Sie <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'API-Schlüssel',
    'Which configured key to send.' => 'Welcher konfigurierte Schlüssel gesendet werden soll.',
    'Endpoint' => 'Endpunkt',
    'Which REST endpoint to call.' => 'Welcher REST-Endpunkt aufgerufen werden soll.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'Numerische Formular- oder Übermittlungs-ID.',
    'Form handle' => 'Formular-Handle',
    'Form handle (the slug, not the title).' => 'Formular-Handle (der Slug, nicht der Titel).',
    'formHandle (optional)' => 'formHandle (optional)',
    'Filter submissions to one form.' => 'Übermittlungen auf ein Formular eingrenzen.',
    'dateFrom (optional)' => 'dateFrom (optional)',
    'dateTo (optional)' => 'dateTo (optional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Test ausführen',
    'Result' => 'Ergebnis',
    'Status:' => 'Status:',
    'Time:' => 'Zeit:',
    'Equivalent curl' => 'Äquivalenter curl-Befehl',
    'Response headers' => 'Antwort-Header',
    'Response body' => 'Antwort-Body',
];
