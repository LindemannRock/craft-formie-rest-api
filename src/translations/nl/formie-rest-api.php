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
    // Navigation
    'API Keys' => 'API-sleutels',
    'Settings' => 'Instellingen',
    'Plugins' => 'Plugins',
    'General' => 'Algemeen',
    'Interface' => 'Interface',
    'Logs' => 'Logboeken',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Instellingen beheren',
    'Manage API keys' => 'API-sleutels beheren',
    'Create API keys' => 'API-sleutels aanmaken',
    'Edit API keys' => 'API-sleutels bewerken',
    'Revoke API keys' => 'API-sleutels intrekken',
    'View system logs' => 'Systeemlogboeken bekijken',
    'Download system logs' => 'Systeemlogboeken downloaden',

    // Common
    'Name' => 'Naam',
    'Status' => 'Status',
    'Actions' => 'Acties',
    'All' => 'Alle',
    'Enable' => 'Inschakelen',
    'Disable' => 'Uitschakelen',
    'Enabled' => 'Ingeschakeld',
    'Disabled' => 'Uitgeschakeld',
    'Edit' => 'Bewerken',
    'Save' => 'Opslaan',
    'Save and continue editing' => 'Opslaan en doorgaan met aanpassen',
    'Set status' => 'Status instellen',
    'Never' => 'Nooit',
    'Created at' => 'Gemaakt op',
    'Updated at' => 'Geüpdatet op',

    // Controller messages
    "Couldn't save settings." => 'Instellingen konden niet worden opgeslagen.',
    'Settings saved.' => 'Instellingen opgeslagen.',
    'API key created' => 'API-sleutel aangemaakt',
    'API key saved' => 'API-sleutel opgeslagen',
    'API key revoked' => 'API-sleutel ingetrokken',
    'Couldn’t save API key' => 'Kan API-sleutel niet opslaan',
    'Couldn’t revoke API key' => 'Kan API-sleutel niet intrekken',
    'API key not found' => 'API-sleutel niet gevonden',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 API-sleutel ingetrokken} other{# API-sleutels ingetrokken}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 API-sleutel ingeschakeld} other{# API-sleutels ingeschakeld}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 API-sleutel uitgeschakeld} other{# API-sleutels uitgeschakeld}}',
    'Couldn’t enable API keys' => 'Kan API-sleutels niet inschakelen',
    'Couldn’t disable API keys' => 'Kan API-sleutels niet uitschakelen',
    'Couldn’t revoke API keys' => 'Kan API-sleutels niet intrekken',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Ingeschakelde sleutels moeten alle formulieren of ten minste één specifiek formulier toestaan.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Ongeldige IP-whitelist-invoer: "{entry}". Gebruik een enkel IP-adres of een CIDR-bereik (bijv. 203.0.113.5 of 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Algemene instellingen',

    // Settings: Interface
    'Interface Settings' => 'Interface-instellingen',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Er zijn nog geen API-sleutels aangemaakt. Maak een sleutel per verbruiker aan om de toegang tot de REST API te beheren.',

    // Index page
    'Allowed forms' => 'Toegestane formulieren',
    'Signing' => 'Ondertekening',
    'Expires' => 'Verloopt',
    'Last used' => 'Laatst gebruikt',
    'Expired' => 'Verlopen',
    'No API keys yet.' => 'Nog geen API-sleutels.',
    'Search API keys...' => 'API-sleutels zoeken...',
    'API key' => 'API-sleutel',
    'API keys' => 'API-sleutels',
    'All Forms' => 'Alle formulieren',
    'form' => 'formulier',
    'forms' => 'formulieren',
    'No forms allowed — this key cannot be used until you add some.' => 'Geen formulieren toegestaan — deze sleutel kan niet worden gebruikt totdat u er enkele toevoegt.',
    'Revoke' => 'Intrekken',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Weet u zeker dat u deze API-sleutel wilt intrekken? Aanroepers die deze gebruiken verliezen direct hun toegang.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Weet u zeker dat u 1 API-sleutel wilt intrekken? Aanroepers die deze gebruiken verliezen direct hun toegang. Dit kan niet ongedaan worden gemaakt.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Weet u zeker dat u {count} API-sleutels wilt intrekken? Aanroepers die deze gebruiken verliezen direct hun toegang. Dit kan niet ongedaan worden gemaakt.',
    'Prefix' => 'Prefix',
    'None' => 'Geen',

    // Edit page
    'New API Key' => 'Nieuwe API-sleutel',
    'Edit API Key' => 'API-sleutel bewerken',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'Een beschrijvend label zodat u deze sleutel in de lijst kunt herkennen — doorgaans de verbruiker waarbij de sleutel hoort. Niet zichtbaar voor aanroepers.',
    'All forms (current and future)' => 'Alle formulieren (huidig en toekomstig)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Indien ingeschakeld, kan deze sleutel elk formulier lezen — inclusief formulieren die na de sleutel worden aangemaakt. Indien uitgeschakeld, kiest u hieronder specifieke formulieren.',
    'Specific forms' => 'Specifieke formulieren',
    'Tick each form this key can read.' => 'Vink elk formulier aan dat deze sleutel mag lezen.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Er zijn nog geen formulieren. Maak een formulier aan voordat deze sleutel nuttig kan zijn.',
    'IP whitelist' => 'IP-whitelist',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Eén invoer per regel. Gebruik een enkel IP-adres (<code>203.0.113.5</code>) of een CIDR-bereik (<code>192.168.1.0/24</code>), IPv4 of IPv6. Laat leeg om alle IP-adressen toe te staan.',
    'Require signing' => 'Ondertekening vereisen',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Indien ingeschakeld, moet elke aanvraag een geldige HMAC-SHA256-handtekening bevatten die berekend is met het ondertekeningsgeheim van deze sleutel.',
    'Read submissions' => 'Inzendingen lezen',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Indien uitgeschakeld, is deze sleutel beperkt tot de formulier-endpoints en kan geen inzendingsgegevens lezen.',
    'Rate limit' => 'Snelheidslimiet',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Beperk de verzoekssnelheid in verzoeken per uur. Laat leeg voor de standaardwaarde (100/uur).',
    'Valid until' => 'Geldig tot',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Optionele vervaldatum en -tijd. Laat leeg voor geen vervaldatum.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Uitschakelen deactiveert de sleutel zonder deze te verwijderen. Intrekken (verwijderen) verwijdert de sleutel permanent.',
    'Copy this API key now — it will never be shown again.' => 'Kopieer deze API-sleutel nu — deze wordt nooit meer getoond.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} bewaart alleen een hash. Als u deze waarde kwijtraakt, moet u een nieuwe sleutel aanmaken.',
    'Copy this signing secret now — it will never be shown again.' => 'Kopieer dit ondertekeningsgeheim nu — het wordt nooit meer getoond.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'De aanroeper gebruikt het om elke aanvraag te ondertekenen (HMAC-SHA256). Lever het samen met de API-sleutel af via een beveiligd kanaal.',

    // Test page
    'Test API' => 'API testen',
    'Test API Endpoints' => 'API-endpoints testen',
    'Send a request to the local API with one of your API keys, and inspect the response.' => 'Verzend een aanvraag naar de lokale API met een van uw API-sleutels en inspecteer het antwoord.',
    'Developer resources' => 'Ontwikkelaarsbronnen',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Download de Postman-collectie en -omgeving om de Formie REST API buiten Craft te testen.',
    'Download Postman collection' => 'Postman-collectie downloaden',
    'API Key' => 'API-sleutel',
    'Paste an API key to test.' => 'Plak een API-sleutel om te testen.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Plak de volledige sleutel (fra_...). Alleen voor deze test gebruikt — nooit opgeslagen.',
    'Signing secret' => 'Ondertekeningsgeheim',
    'Leave empty if the key does not require signing.' => 'Laat leeg als de sleutel geen ondertekening vereist.',
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
    'fields (optional)' => 'fields (optioneel)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Test uitvoeren',
    'Result' => 'Resultaat',
    'Status:' => 'Status:',
    'Time:' => 'Duur:',
    'Equivalent curl' => 'Equivalent curl-commando',
    'Response headers' => 'Antwoordheaders',
    'Response body' => 'Antwoordbody',
    'Running...' => 'Bezig met uitvoeren...',
    'Error:' => 'Fout:',
    'Unknown error' => 'Onbekende fout',
];
