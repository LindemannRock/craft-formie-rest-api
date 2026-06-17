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
    'API Keys' => 'API-Schlüssel',
    'Settings' => 'Einstellungen',
    'Plugins' => 'Plugins',
    'General' => 'Allgemein',
    'Interface' => 'Oberfläche',
    'Logs' => 'Protokolle',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Einstellungen verwalten',
    'Manage API keys' => 'API-Schlüssel verwalten',
    'Create API keys' => 'API-Schlüssel erstellen',
    'Edit API keys' => 'API-Schlüssel bearbeiten',
    'Revoke API keys' => 'API-Schlüssel widerrufen',
    'View system logs' => 'Systemprotokolle anzeigen',
    'Download system logs' => 'Systemprotokolle herunterladen',

    // Common
    'Name' => 'Name',
    'Status' => 'Status',
    'Actions' => 'Aktionen',
    'All' => 'Alle',
    'Enable' => 'Aktivieren',
    'Disable' => 'Deaktivieren',
    'Enabled' => 'Aktiviert',
    'Disabled' => 'Deaktiviert',
    'Edit' => 'Bearbeiten',
    'Save' => 'Speichern',
    'Save and continue editing' => 'Speichern und mit Bearbeitung fortfahren',
    'Set status' => 'Status festlegen',
    'Never' => 'Nie',
    'Created at' => 'Erstellt am',
    'Updated at' => 'Aktualisiert am',

    // Controller messages
    "Couldn't save settings." => 'Einstellungen konnten nicht gespeichert werden.',
    'Settings saved.' => 'Einstellungen gespeichert.',
    'API key created' => 'API-Schlüssel erstellt',
    'API key saved' => 'API-Schlüssel gespeichert',
    'API key revoked' => 'API-Schlüssel widerrufen',
    'Couldn’t save API key' => 'API-Schlüssel konnte nicht gespeichert werden',
    'Couldn’t revoke API key' => 'API-Schlüssel konnte nicht widerrufen werden',
    'API key not found' => 'API-Schlüssel nicht gefunden',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 API-Schlüssel widerrufen} other{# API-Schlüssel widerrufen}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 API-Schlüssel aktiviert} other{# API-Schlüssel aktiviert}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 API-Schlüssel deaktiviert} other{# API-Schlüssel deaktiviert}}',
    'Couldn’t enable API keys' => 'API-Schlüssel konnten nicht aktiviert werden',
    'Couldn’t disable API keys' => 'API-Schlüssel konnten nicht deaktiviert werden',
    'Couldn’t revoke API keys' => 'API-Schlüssel konnten nicht widerrufen werden',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Aktivierte Schlüssel müssen alle Formulare oder mindestens ein bestimmtes Formular erlauben.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Ungültiger IP-Whitelist-Eintrag: „{entry}". Verwenden Sie eine einzelne IP oder einen CIDR-Bereich (z. B. 203.0.113.5 oder 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Allgemeine Einstellungen',

    // Settings: Interface
    'Interface Settings' => 'Oberflächen-Einstellungen',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Es wurden noch keine API-Schlüssel erstellt. Erstellen Sie einen Schlüssel pro Verbraucher, um den Zugriff auf die REST API zu steuern.',

    // Index page
    'Allowed forms' => 'Erlaubte Formulare',
    'Signing' => 'Signatur',
    'Expires' => 'Läuft ab',
    'Last used' => 'Zuletzt verwendet',
    'Expired' => 'Abgelaufen',
    'No API keys yet.' => 'Noch keine API-Schlüssel.',
    'Search API keys...' => 'API-Schlüssel durchsuchen...',
    'API key' => 'API-Schlüssel',
    'API keys' => 'API-Schlüssel',
    'All Forms' => 'Alle Formulare',
    'form' => 'Formular',
    'forms' => 'Formulare',
    'No forms allowed — this key cannot be used until you add some.' => 'Keine Formulare erlaubt – dieser Schlüssel kann erst verwendet werden, wenn Sie welche hinzufügen.',
    'Revoke' => 'Widerrufen',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Möchten Sie diesen API-Schlüssel wirklich widerrufen? Alle Aufrufer, die ihn verwenden, verlieren sofort den Zugriff.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Möchten Sie wirklich 1 API-Schlüssel widerrufen? Alle Aufrufer, die ihn verwenden, verlieren sofort den Zugriff. Dies kann nicht rückgängig gemacht werden.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Möchten Sie wirklich {count} API-Schlüssel widerrufen? Alle Aufrufer, die sie verwenden, verlieren sofort den Zugriff. Dies kann nicht rückgängig gemacht werden.',
    'Prefix' => 'Präfix',
    'None' => 'Keine',

    // Edit page
    'New API Key' => 'Neuer API-Schlüssel',
    'Edit API Key' => 'API-Schlüssel bearbeiten',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'Eine beschreibende Bezeichnung, damit Sie diesen Schlüssel in der Liste identifizieren können — üblicherweise der Verbraucher, zu dem er gehört. Wird Aufrufern nicht offengelegt.',
    'All forms (current and future)' => 'Alle Formulare (aktuelle und zukünftige)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Wenn aktiviert, kann dieser Schlüssel jedes Formular lesen — einschließlich Formulare, die nach dem Schlüssel erstellt werden. Wenn deaktiviert, wählen Sie unten bestimmte Formulare aus.',
    'Specific forms' => 'Bestimmte Formulare',
    'Tick each form this key can read.' => 'Markieren Sie jedes Formular, das dieser Schlüssel lesen darf.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Es gibt noch keine Formulare. Erstellen Sie ein Formular, bevor dieser Schlüssel sinnvoll genutzt werden kann.',
    'IP whitelist' => 'IP-Whitelist',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Ein Eintrag pro Zeile. Verwenden Sie eine einzelne IP (<code>203.0.113.5</code>) oder einen CIDR-Bereich (<code>192.168.1.0/24</code>), IPv4 oder IPv6. Leer lassen, um alle IPs zuzulassen.',
    'Require signing' => 'Signatur erforderlich',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Wenn aktiviert, muss jede Anfrage eine gültige HMAC-SHA256-Signatur tragen, die mit dem Signatur-Geheimnis dieses Schlüssels berechnet wurde.',
    'Read submissions' => 'Übermittlungen lesen',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Wenn deaktiviert, ist dieser Schlüssel auf die Formular-Endpunkte beschränkt und kann keine Übermittlungsdaten lesen.',
    'Rate limit' => 'Ratenbegrenzung',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Die Anfragerate in Anfragen pro Stunde begrenzen. Leer lassen für den Standardwert (100/Stunde).',
    'Valid until' => 'Gültig bis',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Optionales Ablaufdatum. Leer lassen für keinen Ablauf.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Das Deaktivieren setzt den Schlüssel außer Kraft, ohne ihn zu löschen. Widerrufen (Löschen) entfernt den Schlüssel dauerhaft.',
    'Copy this API key now — it will never be shown again.' => 'Kopieren Sie diesen API-Schlüssel jetzt – er wird nie wieder angezeigt.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} speichert nur einen Hash. Wenn Sie diesen Wert verlieren, müssen Sie einen neuen Schlüssel erstellen.',
    'Copy this signing secret now — it will never be shown again.' => 'Kopieren Sie dieses Signatur-Geheimnis jetzt – es wird nie wieder angezeigt.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'Der Aufrufer verwendet es, um jede Anfrage zu signieren (HMAC-SHA256). Übermitteln Sie es zusammen mit dem API-Schlüssel über einen sicheren Kanal.',

    // Test page
    'Test API' => 'API testen',
    'Test API Endpoints' => 'API-Endpunkte testen',
    'Send a request to the local API with one of your API keys, and inspect the response.' => 'Sendet eine Anfrage an die lokale API mit einem Ihrer API-Schlüssel und zeigt die Antwort an.',
    'Developer resources' => 'Entwicklerressourcen',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Laden Sie die Postman-Sammlung und Umgebung herunter, um die Formie REST API außerhalb von Craft zu testen.',
    'Download Postman collection' => 'Postman-Sammlung herunterladen',
    'API Key' => 'API-Schlüssel',
    'Paste an API key to test.' => 'Fügen Sie einen API-Schlüssel zum Testen ein.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Fügen Sie den vollständigen Schlüssel ein (fra_...). Wird nur für diesen Test verwendet — niemals gespeichert.',
    'Signing secret' => 'Signatur-Geheimnis',
    'Leave empty if the key does not require signing.' => 'Leer lassen, wenn der Schlüssel keine Signatur erfordert.',
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
    'Running...' => 'Wird ausgeführt...',
    'Error:' => 'Fehler:',
    'Unknown error' => 'Unbekannter Fehler',
];
