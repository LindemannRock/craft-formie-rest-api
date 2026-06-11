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
    'API Keys' => 'API-nøgler',
    'Settings' => 'Indstillinger',
    'Plugins' => 'Plugins',
    'General' => 'Generelt',
    'Interface' => 'Brugerflade',
    'Logs' => 'Logfiler',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Administrer indstillinger',
    'Manage API keys' => 'Administrer API-nøgler',
    'Create API keys' => 'Opret API-nøgler',
    'Edit API keys' => 'Rediger API-nøgler',
    'Revoke API keys' => 'Tilbagekald API-nøgler',
    'View system logs' => 'Vis systemlogfiler',
    'Download system logs' => 'Download systemlogfiler',

    // Common
    'Name' => 'Navn',
    'Status' => 'Status',
    'Actions' => 'Handlinger',
    'All' => 'Alle',
    'Enable' => 'Aktiver',
    'Disable' => 'Deaktiver',
    'Enabled' => 'Aktiveret',
    'Disabled' => 'Deaktiveret',
    'Edit' => 'Rediger',
    'Save' => 'Gem',
    'Save and continue editing' => 'Gem og fortsæt med redigering',
    'Set status' => 'Indstil status',
    'Never' => 'Aldrig',
    'Created at' => 'Oprettet',
    'Updated at' => 'Opdateret',

    // Controller messages
    "Couldn't save settings." => 'Indstillingerne kunne ikke gemmes.',
    'Settings saved.' => 'Indstillinger gemt.',
    'Selected API key is not configured.' => 'Den valgte API-nøgle er ikke konfigureret.',
    'API key created' => 'API-nøgle oprettet',
    'API key saved' => 'API-nøgle gemt',
    'API key revoked' => 'API-nøgle tilbagekaldt',
    'Couldn’t save API key' => 'Kunne ikke gemme API-nøgle',
    'Couldn’t revoke API key' => 'Kunne ikke tilbagekalde API-nøgle',
    'API key not found' => 'API-nøgle ikke fundet',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 API-nøgle tilbagekaldt} other{# API-nøgler tilbagekaldt}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 API-nøgle aktiveret} other{# API-nøgler aktiveret}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 API-nøgle deaktiveret} other{# API-nøgler deaktiveret}}',
    'Couldn’t enable API keys' => 'Kunne ikke aktivere API-nøgler',
    'Couldn’t disable API keys' => 'Kunne ikke deaktivere API-nøgler',
    'Couldn’t revoke API keys' => 'Kunne ikke tilbagekalde API-nøgler',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Aktiverede nøgler skal tillade alle formularer eller mindst én specifik formular.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Ugyldig IP-hvidlistepost: "{entry}". Brug en enkelt IP eller et CIDR-interval (f.eks. 203.0.113.5 eller 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Generelle indstillinger',

    // Settings: Interface
    'Interface Settings' => 'Brugerflade-indstillinger',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Der er endnu ikke oprettet nogen API-nøgler. Opret en nøgle pr. konsument for at styre adgangen til REST API.',

    // Index page
    'Allowed forms' => 'Tilladte formularer',
    'Signing' => 'Signering',
    'Expires' => 'Udløber',
    'Last used' => 'Sidst brugt',
    'Expired' => 'Udløbet',
    'No API keys yet.' => 'Ingen API-nøgler endnu.',
    'Search API keys...' => 'Søg API-nøgler...',
    'API key' => 'API-nøgle',
    'API keys' => 'API-nøgler',
    'All Forms' => 'Alle formularer',
    'form' => 'formular',
    'forms' => 'formularer',
    'No forms allowed — this key cannot be used until you add some.' => 'Ingen formularer tilladt — denne nøgle kan ikke bruges, før du tilføjer nogle.',
    'Revoke' => 'Tilbagekald',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Er du sikker på, at du vil tilbagekalde denne API-nøgle? Alle kaldere, der bruger den, mister øjeblikkeligt adgangen.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Er du sikker på, at du vil tilbagekalde 1 API-nøgle? Alle kaldere, der bruger den, mister øjeblikkeligt adgangen. Dette kan ikke fortrydes.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Er du sikker på, at du vil tilbagekalde {count} API-nøgler? Alle kaldere, der bruger dem, mister øjeblikkeligt adgangen. Dette kan ikke fortrydes.',
    'Prefix' => 'Præfiks',
    'None' => 'Ingen',

    // Edit page
    'New API Key' => 'Ny API-nøgle',
    'Edit API Key' => 'Rediger API-nøgle',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'En beskrivende etiket, så du kan identificere denne nøgle i listen — typisk den konsument den tilhører. Ikke synlig for kaldere.',
    'All forms (current and future)' => 'Alle formularer (nuværende og fremtidige)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Når aktiveret kan denne nøgle læse alle formularer — inklusive formularer oprettet efter nøglen. Når deaktiveret, vælg specifikke formularer nedenfor.',
    'Specific forms' => 'Specifikke formularer',
    'Tick each form this key can read.' => 'Markér hver formular, som denne nøgle kan læse.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Der er endnu ingen formularer. Opret en formular, før denne nøgle kan være nyttig.',
    'IP whitelist' => 'IP-hvidliste',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Én post pr. linje. Brug en enkelt IP (<code>203.0.113.5</code>) eller et CIDR-interval (<code>192.168.1.0/24</code>), IPv4 eller IPv6. Lad stå tom for at tillade alle IP-adresser.',
    'Require signing' => 'Kræv signering',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Når aktiveret skal hver anmodning bære en gyldig HMAC-SHA256-signatur beregnet med denne nøgles signeringshemlighed.',
    'Read submissions' => 'Læs indsendelser',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Når deaktiveret er denne nøgle begrænset til formularendpoints og kan ikke læse indsendelsesdata.',
    'Rate limit' => 'Hastighedsgrænse',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Begræns forespørgselshastigheden i forespørgsler pr. time. Lad stå tom for standardværdien (100/time).',
    'Valid until' => 'Gyldig indtil',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Valgfri udløbsdato og -tid. Lad stå tom for ingen udløb.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Deaktivering deaktiverer nøglen uden at slette den. Tilbagekald (slet) fjerner nøglen permanent.',
    'Copy this API key now — it will never be shown again.' => 'Kopiér denne API-nøgle nu — den vises aldrig igen.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} gemmer kun et hash. Hvis du mister denne værdi, skal du oprette en ny nøgle.',
    'Copy this signing secret now — it will never be shown again.' => 'Kopiér denne signeringshemlighed nu — den vises aldrig igen.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'Kalderen bruger den til at signere hver anmodning (HMAC-SHA256). Lever den sammen med API-nøglen via en sikker kanal.',

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
    'Pasted key' => 'Indsat nøgle',
    'Paste an API key to test.' => 'Indsæt en API-nøgle for at teste.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Indsæt hele nøglen (fra_...). Bruges kun til denne test — gemmes aldrig.',
    'Signing secret' => 'Signeringshemlighed',
    'Leave empty if the key does not require signing.' => 'Lad stå tomt, hvis nøglen ikke kræver signering.',
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
    'fields (optional)' => 'fields (valgfri)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Kør test',
    'Result' => 'Resultat',
    'Status:' => 'Status:',
    'Time:' => 'Tid:',
    'Equivalent curl' => 'Tilsvarende curl-kommando',
    'Response headers' => 'Svarheaders',
    'Response body' => 'Svarbody',
    'Running...' => 'Kører...',
    'Error:' => 'Fejl:',
    'Unknown error' => 'Ukendt fejl',
];
