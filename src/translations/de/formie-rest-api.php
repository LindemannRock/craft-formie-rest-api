<?php
/**
 * Formie REST API translation file (German)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'API-Schlüssel verwalten, Endpunkte absichern und Formie-Datenantworten im Plugin-Einstellungsbereich testen.',
    'Open Formie REST API' => 'Formie REST API öffnen',
    // Navigation
    'Settings' => 'Einstellungen',
    'Plugins' => 'Plugins',
    'General' => 'Allgemein',
    'Interface' => 'Oberfläche',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Einstellungen verwalten',

    // Controller messages
    "Couldn't save settings." => 'Einstellungen konnten nicht gespeichert werden.',
    'Settings saved.' => 'Einstellungen gespeichert.',
    'Selected API key is not configured.' => 'Der ausgewählte API-Schlüssel ist nicht konfiguriert.',

    // Settings: General
    'General Settings' => 'Allgemeine Einstellungen',

    // Settings: Interface
    'Interface Settings' => 'Oberflächen-Einstellungen',

    // Settings: Configuration warning
    'COPIED' => 'KOPIERT',
    'COPY' => 'KOPIEREN',
    'Configuration Required' => 'Konfiguration erforderlich',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Erzeugen Sie separate Schlüssel pro Umgebung — kopieren Sie niemals Produktionsschlüssel in Staging oder Entwicklung.',
    'No API keys configured.' => 'Keine API-Schlüssel konfiguriert.',
    'Run one of these commands in your terminal:' => 'Führen Sie einen dieser Befehle im Terminal aus:',
    'Standard:' => 'Standard:',
    'The plugin will reject every request until at least one key is set.' => 'Das Plugin lehnt jede Anfrage ab, bis mindestens ein Schlüssel gesetzt ist.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Dies schreibt {keys} und die zugehörigen Signatur-Geheimnisse in Ihre {file}-Datei.',
    'Warning:' => 'Warnung:',
    'error' => 'Fehler',

    // Test page
    'Test API' => 'API testen',
    'Test API Endpoints' => 'API-Endpunkte testen',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Sendet eine Anfrage an die lokale API mit einem der konfigurierten Schlüssel und zeigt die Antwort an.',
    'Developer resources' => 'Entwicklerressourcen',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Laden Sie die Postman-Sammlung und Umgebung herunter, um die Formie REST API außerhalb von Craft zu testen.',
    'Download Postman collection' => 'Postman-Sammlung herunterladen',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'Keine API-Schlüssel konfiguriert. Setzen Sie FORMIE_API_KEY (und optional FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in Ihrer .env-Datei oder führen Sie <code>php craft formie-rest-api/security/generate-key</code> aus (mit DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
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
    'fields (optional)' => 'fields (optional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Test ausführen',
    'Result' => 'Ergebnis',
    'Status:' => 'Status:',
    'Time:' => 'Zeit:',
    'Equivalent curl' => 'Äquivalenter curl-Befehl',
    'Response headers' => 'Antwort-Header',
    'Response body' => 'Antwort-Body',
    'Running…' => 'Wird ausgeführt…',
    'Error:' => 'Fehler:',
    'Unknown error' => 'Unbekannter Fehler',
];
