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
    'Interface' => 'Grensesnitt',
    'Logs' => 'Logger',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Administrer innstillinger',
    'Manage API keys' => 'Administrer API-nøkler',
    'Create API keys' => 'Opprett API-nøkler',
    'Edit API keys' => 'Rediger API-nøkler',
    'Revoke API keys' => 'Tilbakekall API-nøkler',
    'View system logs' => 'Vis systemlogger',
    'Download system logs' => 'Last ned systemlogger',

    // Common
    'Name' => 'Navn',
    'Status' => 'Status',
    'Actions' => 'Handlinger',
    'All' => 'Alle',
    'Enable' => 'Aktiver',
    'Disable' => 'Deaktiver',
    'Enabled' => 'Aktivert',
    'Disabled' => 'Deaktivert',
    'Edit' => 'Rediger',
    'Save' => 'Lagre',
    'Save and continue editing' => 'Lagre og fortsett redigering',
    'Set status' => 'Sett status',
    'Never' => 'Aldri',
    'Created at' => 'Opprettet',
    'Updated at' => 'Oppdatert',

    // Controller messages
    "Couldn't save settings." => 'Innstillingene kunne ikke lagres.',
    'Settings saved.' => 'Innstillinger lagret.',
    'Selected API key is not configured.' => 'Den valgte API-nøkkelen er ikke konfigurert.',
    'API key created' => 'API-nøkkel opprettet',
    'API key saved' => 'API-nøkkel lagret',
    'API key revoked' => 'API-nøkkel tilbakekalt',
    'Couldn’t save API key' => 'Kunne ikke lagre API-nøkkel',
    'Couldn’t revoke API key' => 'Kunne ikke tilbakekalle API-nøkkel',
    'API key not found' => 'API-nøkkel ikke funnet',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 API-nøkkel tilbakekalt} other{# API-nøkler tilbakekalt}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 API-nøkkel aktivert} other{# API-nøkler aktivert}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 API-nøkkel deaktivert} other{# API-nøkler deaktivert}}',
    'Couldn’t enable API keys' => 'Kunne ikke aktivere API-nøkler',
    'Couldn’t disable API keys' => 'Kunne ikke deaktivere API-nøkler',
    'Couldn’t revoke API keys' => 'Kunne ikke tilbakekalle API-nøkler',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Aktiverte nøkler må tillate alle skjemaer eller minst ett spesifikt skjema.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Ugyldig IP-hvitelisteoppføring: "{entry}". Bruk en enkelt IP eller et CIDR-område (f.eks. 203.0.113.5 eller 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Generelle innstillinger',

    // Settings: Interface
    'Interface Settings' => 'Grensesnittinnstillinger',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Ingen API-nøkler er opprettet ennå. Opprett en nøkkel per forbruker for å kontrollere tilgangen til REST API.',

    // Index page
    'Allowed forms' => 'Tillatte skjemaer',
    'Signing' => 'Signering',
    'Expires' => 'Utløper',
    'Last used' => 'Sist brukt',
    'Expired' => 'Utløpt',
    'No API keys yet.' => 'Ingen API-nøkler ennå.',
    'Search API keys...' => 'Søk API-nøkler...',
    'API key' => 'API-nøkkel',
    'API keys' => 'API-nøkler',
    'All Forms' => 'Alle skjemaer',
    'form' => 'skjema',
    'forms' => 'skjemaer',
    'No forms allowed — this key cannot be used until you add some.' => 'Ingen skjemaer er tillatt — denne nøkkelen kan ikke brukes før du legger til noen.',
    'Revoke' => 'Tilbakekall',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Er du sikker på at du vil tilbakekalle denne API-nøkkelen? Alle som bruker den vil umiddelbart miste tilgang.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Er du sikker på at du vil tilbakekalle 1 API-nøkkel? Alle som bruker den vil umiddelbart miste tilgang. Dette kan ikke angres.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Er du sikker på at du vil tilbakekalle {count} API-nøkler? Alle som bruker dem vil umiddelbart miste tilgang. Dette kan ikke angres.',
    'Prefix' => 'Prefiks',
    'None' => 'Ingen',

    // Edit page
    'New API Key' => 'Ny API-nøkkel',
    'Edit API Key' => 'Rediger API-nøkkel',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'En beskrivende etikett slik at du kan identifisere denne nøkkelen i listen — vanligvis forbrukeren den tilhører. Eksponeres ikke til de som kaller den.',
    'All forms (current and future)' => 'Alle skjemaer (nåværende og fremtidige)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Når aktivert kan denne nøkkelen lese hvert skjema — inkludert skjemaer opprettet etter nøkkelen. Når deaktivert, velg spesifikke skjemaer nedenfor.',
    'Specific forms' => 'Spesifikke skjemaer',
    'Tick each form this key can read.' => 'Hak av hvert skjema denne nøkkelen kan lese.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Ingen skjemaer finnes ennå. Opprett et skjema før denne nøkkelen kan være nyttig.',
    'IP whitelist' => 'IP-hviteliste',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Én oppføring per linje. Bruk en enkelt IP (<code>203.0.113.5</code>) eller et CIDR-område (<code>192.168.1.0/24</code>), IPv4 eller IPv6. La stå tomt for å tillate alle IP-er.',
    'Require signing' => 'Krev signering',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Når den er på, må hver forespørsel ha en gyldig HMAC-SHA256-signatur beregnet med denne nøkkelens signeringshemmelighet.',
    'Read submissions' => 'Les innsendinger',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Når deaktivert er denne nøkkelen begrenset til skjema-endepunktene — den kan ikke få tilgang til innsendingsdata.',
    'Rate limit' => 'Hastighetsgrense',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Begrens forespørselshastigheten i forespørsler per time. La stå tom for standardverdien (100/time).',
    'Valid until' => 'Gyldig til',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Valgfri utløpsdato og -tid. La stå tom for ingen utløp.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Deaktivering deaktiverer nøkkelen uten å slette den. Tilbakekall (slett) fjerner nøkkelen permanent.',
    'Copy this API key now — it will never be shown again.' => 'Kopier denne API-nøkkelen nå — den vil aldri vises igjen.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} lagrer kun en hash. Hvis du mister denne verdien må du opprette en ny nøkkel.',
    'Copy this signing secret now — it will never be shown again.' => 'Kopier denne signeringshemmeligheten nå — den vil aldri vises igjen.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'Kalleren bruker den til å signere hver forespørsel (HMAC-SHA256). Lever den sammen med API-nøkkelen via en sikker kanal.',

    // Test page
    'Test API' => 'Test API',
    'Test API Endpoints' => 'Test API-endepunkter',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Send en forespørsel til den lokale API-en med en av de konfigurerte nøklene, og inspiser svaret.',
    'Developer resources' => 'Utviklerressurser',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Last ned Postman-samlingen og -miljøet for å teste Formie REST API utenfor Craft.',
    'Download Postman collection' => 'Last ned Postman-samling',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'Ingen API-nøkler konfigurert. Angi FORMIE_API_KEY (og eventuelt FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) i .env-filen din, eller kjør <code>php craft formie-rest-api/security/generate-key</code> (med DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'API-nøkkel',
    'Which configured key to send.' => 'Hvilken konfigurert nøkkel som skal sendes.',
    'Pasted key' => 'Limt inn nøkkel',
    'Paste an API key to test.' => 'Lim inn en API-nøkkel for å teste.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Lim inn hele nøkkelen (fra_...). Brukes kun til denne testen — lagres aldri.',
    'Signing secret' => 'Signeringshemmelighet',
    'Leave empty if the key does not require signing.' => 'La stå tomt hvis nøkkelen ikke krever signering.',
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
    'fields (optional)' => 'fields (valgfri)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Kjør test',
    'Result' => 'Resultat',
    'Status:' => 'Status:',
    'Time:' => 'Tid:',
    'Equivalent curl' => 'Tilsvarende curl-kommando',
    'Response headers' => 'Svar-headere',
    'Response body' => 'Svar-body',
    'Running...' => 'Kjører...',
    'Error:' => 'Feil:',
    'Unknown error' => 'Ukjent feil',
];
