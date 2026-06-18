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
    'API Keys' => 'API-nycklar',
    'Settings' => 'Inställningar',
    'Plugins' => 'Plugins',
    'General' => 'Allmänt',
    'Interface' => 'Gränssnitt',
    'Logs' => 'Loggar',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Hantera inställningar',
    'Manage API keys' => 'Hantera API-nycklar',
    'Create API keys' => 'Skapa API-nycklar',
    'Edit API keys' => 'Redigera API-nycklar',
    'Revoke API keys' => 'Återkalla API-nycklar',
    'View system logs' => 'Visa systemloggar',
    'Download system logs' => 'Ladda ner systemloggar',

    // Common
    'Name' => 'Namn',
    'Status' => 'Status',
    'Actions' => 'Åtgärder',
    'All' => 'Alla',
    'Enable' => 'Aktivera',
    'Disable' => 'Inaktivera',
    'Enabled' => 'Aktiverad',
    'Disabled' => 'Inaktiverad',
    'Edit' => 'Redigera',
    'Save' => 'Spara',
    'Save and continue editing' => 'Spara och fortsätt redigera',
    'Set status' => 'Ställ in status',
    'Never' => 'Aldrig',
    'Created at' => 'Skapad',
    'Updated at' => 'Uppdaterad',

    // Controller messages
    "Couldn't save settings." => 'Det gick inte att spara inställningarna.',
    'Settings saved.' => 'Inställningar sparade.',
    'API key created' => 'API-nyckel skapad',
    'API key saved' => 'API-nyckel sparad',
    'API key revoked' => 'API-nyckel återkallad',
    'Couldn’t save API key' => 'Kunde inte spara API-nyckel',
    'Couldn’t revoke API key' => 'Kunde inte återkalla API-nyckel',
    'API key not found' => 'API-nyckel hittades inte',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 API-nyckel återkallad} other{# API-nycklar återkallade}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 API-nyckel aktiverad} other{# API-nycklar aktiverade}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 API-nyckel inaktiverad} other{# API-nycklar inaktiverade}}',
    'Couldn’t enable API keys' => 'Kunde inte aktivera API-nycklar',
    'Couldn’t disable API keys' => 'Kunde inte inaktivera API-nycklar',
    'Couldn’t revoke API keys' => 'Kunde inte återkalla API-nycklar',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Aktiverade nycklar måste tillåta alla formulär eller minst ett specifikt formulär.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Ogiltig IP-vitlistspost: "{entry}". Använd en enskild IP eller ett CIDR-intervall (t.ex. 203.0.113.5 eller 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Allmänna inställningar',

    // Settings: Interface
    'Interface Settings' => 'Gränssnittsinställningar',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Inga API-nycklar har skapats ännu. Skapa en nyckel per konsument för att styra åtkomsten till REST API:et.',

    // Index page
    'Allowed forms' => 'Tillåtna formulär',
    'Signing' => 'Signering',
    'Expires' => 'Upphör',
    'Last used' => 'Senast använd',
    'Expired' => 'Utgången',
    'No API keys yet.' => 'Inga API-nycklar ännu.',
    'Search API keys...' => 'Sök API-nycklar...',
    'API key' => 'API-nyckel',
    'API keys' => 'API-nycklar',
    'All Forms' => 'Alla formulär',
    'form' => 'formulär',
    'forms' => 'formulär',
    'No forms allowed — this key cannot be used until you add some.' => 'Inga formulär tillåtna — denna nyckel kan inte användas förrän du lägger till några.',
    'Revoke' => 'Återkalla',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Är du säker på att du vill återkalla denna API-nyckel? Alla anropare som använder den förlorar omedelbart åtkomst.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Är du säker på att du vill återkalla 1 API-nyckel? Alla anropare som använder den förlorar omedelbart åtkomst. Detta kan inte ångras.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Är du säker på att du vill återkalla {count} API-nycklar? Alla anropare som använder dem förlorar omedelbart åtkomst. Detta kan inte ångras.',
    'Prefix' => 'Prefix',
    'None' => 'Ingen',

    // Edit page
    'New API Key' => 'Ny API-nyckel',
    'Edit API Key' => 'Redigera API-nyckel',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'En beskrivande etikett så att du kan identifiera denna nyckel i listan — vanligtvis konsumenten den tillhör. Visas inte för anropare.',
    'All forms (current and future)' => 'Alla formulär (nuvarande och framtida)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'När aktiverat kan denna nyckel läsa alla formulär — inklusive formulär som skapats efter nyckeln. När inaktiverat, välj specifika formulär nedan.',
    'Specific forms' => 'Specifika formulär',
    'Tick each form this key can read.' => 'Markera varje formulär som denna nyckel kan läsa.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Det finns inga formulär ännu. Skapa ett formulär innan denna nyckel kan vara användbar.',
    'IP whitelist' => 'IP-vitlista',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'En post per rad. Använd en enskild IP (<code>203.0.113.5</code>) eller ett CIDR-intervall (<code>192.168.1.0/24</code>), IPv4 eller IPv6. Lämna tomt för att tillåta alla IP-adresser.',
    'Require signing' => 'Kräv signering',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'När aktiverat måste varje begäran bära en giltig HMAC-SHA256-signatur beräknad med denna nyckels signeringshemlighet.',
    'Read submissions' => 'Läs inlämningar',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'När inaktiverat är denna nyckel begränsad till formulärslutpunkterna och kan inte läsa inlämningsdata.',
    'Rate limit' => 'Hastighetsgräns',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Begränsa förfrågningshastigheten i förfrågningar per timme. Lämna tomt för standardvärdet (100/timme).',
    'Valid until' => 'Giltig till',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Valfritt utgångsdatum och tid. Lämna tomt för ingen utgång.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Att inaktivera avaktiverar nyckeln utan att ta bort den. Återkalla (radera) tar bort nyckeln permanent.',
    'Copy this API key now — it will never be shown again.' => 'Kopiera denna API-nyckel nu — den kommer aldrig att visas igen.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} lagrar endast en hash. Om du förlorar detta värde måste du skapa en ny nyckel.',
    'Copy this signing secret now — it will never be shown again.' => 'Kopiera denna signeringshemlighet nu — den kommer aldrig att visas igen.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'Anroparen använder det för att signera varje begäran (HMAC-SHA256). Leverera det tillsammans med API-nyckeln via en säker kanal.',

    // Test page
    'Test API' => 'Testa API',
    'Test API Endpoints' => 'Testa API-slutpunkter',
    'Send a request to the local API with one of your API keys, and inspect the response.' => 'Skicka en begäran till det lokala API:et med en av dina API-nycklar och granska svaret.',
    'Developer resources' => 'Utvecklarresurser',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Ladda ner Postman-samlingen och miljön för att testa Formie REST API utanför Craft.',
    'Download Postman collection' => 'Ladda ner Postman-samling',
    'API Key' => 'API-nyckel',
    'Paste an API key to test.' => 'Klistra in en API-nyckel att testa.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Klistra in hela nyckeln (fra_...). Används endast för detta test — sparas aldrig.',
    'Signing secret' => 'Signeringshemlighet',
    'Leave empty if the key does not require signing.' => 'Lämna tomt om nyckeln inte kräver signering.',
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
    'fields (optional)' => 'fields (valfri)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Kör test',
    'Result' => 'Resultat',
    'Status:' => 'Status:',
    'Time:' => 'Tid:',
    'Equivalent curl' => 'Motsvarande curl-kommando',
    'Response headers' => 'Svarshuvuden',
    'Response body' => 'Svarskropp',
    'Running...' => 'Kör...',
    'Error:' => 'Fel:',
    'Unknown error' => 'Okänt fel',
];
