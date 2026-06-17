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
    'API Keys' => 'Chiavi API',
    'Settings' => 'Impostazioni',
    'Plugins' => 'Plugin',
    'General' => 'Generale',
    'Interface' => 'Interfaccia',
    'Logs' => 'Log',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Gestisci impostazioni',
    'Manage API keys' => 'Gestisci chiavi API',
    'Create API keys' => 'Crea chiavi API',
    'Edit API keys' => 'Modifica chiavi API',
    'Revoke API keys' => 'Revoca chiavi API',
    'View system logs' => 'Visualizza log di sistema',
    'Download system logs' => 'Scarica log di sistema',

    // Common
    'Name' => 'Nome',
    'Status' => 'Stato',
    'Actions' => 'Azioni',
    'All' => 'Tutti',
    'Enable' => 'Abilita',
    'Disable' => 'Disabilita',
    'Enabled' => 'Abilitato',
    'Disabled' => 'Disabilitato',
    'Edit' => 'Modifica',
    'Save' => 'Salva',
    'Save and continue editing' => 'Salva e continua la modifica',
    'Set status' => 'Imposta stato',
    'Never' => 'Mai',
    'Created at' => 'Creato il',
    'Updated at' => 'Aggiornato il',

    // Controller messages
    "Couldn't save settings." => 'Impossibile salvare le impostazioni.',
    'Settings saved.' => 'Impostazioni salvate.',
    'API key created' => 'Chiave API creata',
    'API key saved' => 'Chiave API salvata',
    'API key revoked' => 'Chiave API revocata',
    'Couldn’t save API key' => 'Impossibile salvare la chiave API',
    'Couldn’t revoke API key' => 'Impossibile revocare la chiave API',
    'API key not found' => 'Chiave API non trovata',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 chiave API revocata} other{# chiavi API revocate}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 chiave API abilitata} other{# chiavi API abilitate}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 chiave API disabilitata} other{# chiavi API disabilitate}}',
    'Couldn’t enable API keys' => 'Impossibile abilitare le chiavi API',
    'Couldn’t disable API keys' => 'Impossibile disabilitare le chiavi API',
    'Couldn’t revoke API keys' => 'Impossibile revocare le chiavi API',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Le chiavi abilitate devono consentire tutti i moduli o almeno un modulo specifico.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Voce della lista bianca IP non valida: "{entry}". Utilizzare un singolo IP o un intervallo CIDR (es. 203.0.113.5 o 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Impostazioni generali',

    // Settings: Interface
    'Interface Settings' => 'Impostazioni interfaccia',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Non è ancora stata creata alcuna chiave API. Crei una chiave per consumatore per controllare l\'accesso alla REST API.',

    // Index page
    'Allowed forms' => 'Moduli consentiti',
    'Signing' => 'Firma',
    'Expires' => 'Scade',
    'Last used' => 'Ultimo utilizzo',
    'Expired' => 'Scaduto',
    'No API keys yet.' => 'Nessuna chiave API ancora.',
    'Search API keys...' => 'Cerca chiavi API...',
    'API key' => 'chiave API',
    'API keys' => 'chiavi API',
    'All Forms' => 'Tutti i moduli',
    'form' => 'modulo',
    'forms' => 'moduli',
    'No forms allowed — this key cannot be used until you add some.' => 'Nessun modulo consentito — questa chiave non può essere utilizzata finché non ne aggiunge alcuni.',
    'Revoke' => 'Revoca',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'È sicuro di voler revocare questa chiave API? Tutti i chiamanti che la utilizzano perderanno immediatamente l\'accesso.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'È sicuro di voler revocare 1 chiave API? Tutti i chiamanti che la utilizzano perderanno immediatamente l\'accesso. Questa azione non può essere annullata.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'È sicuro di voler revocare {count} chiavi API? Tutti i chiamanti che le utilizzano perderanno immediatamente l\'accesso. Questa azione non può essere annullata.',
    'Prefix' => 'Prefisso',
    'None' => 'Nessuno',

    // Edit page
    'New API Key' => 'Nuova chiave API',
    'Edit API Key' => 'Modifica chiave API',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'Un\'etichetta descrittiva per identificare questa chiave nell\'elenco — tipicamente il consumatore a cui appartiene. Non esposta ai chiamanti.',
    'All forms (current and future)' => 'Tutti i moduli (attuali e futuri)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Quando attivo, questa chiave può leggere ogni modulo — inclusi i moduli creati dopo la chiave. Quando disattivato, scegli moduli specifici di seguito.',
    'Specific forms' => 'Moduli specifici',
    'Tick each form this key can read.' => 'Spunta ciascun modulo che questa chiave può leggere.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Non esistono ancora moduli. Crea un modulo prima che questa chiave possa essere utile.',
    'IP whitelist' => 'Lista bianca IP',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Una voce per riga. Utilizzare un singolo IP (<code>203.0.113.5</code>) o un intervallo CIDR (<code>192.168.1.0/24</code>), IPv4 o IPv6. Lasciare vuoto per consentire tutti gli IP.',
    'Require signing' => 'Richiedi firma',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Quando attivo, ogni richiesta deve portare una firma HMAC-SHA256 valida calcolata con il segreto di firma di questa chiave.',
    'Read submissions' => 'Leggi gli invii',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Quando disattivato, questa chiave è limitata agli endpoint dei moduli e non può leggere alcun dato di invio.',
    'Rate limit' => 'Limite di frequenza',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Limita la frequenza di richieste in richieste all\'ora. Lascia vuoto per il valore predefinito (100/ora).',
    'Valid until' => 'Valida fino a',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Data e ora di scadenza opzionale. Lascia vuoto per nessuna scadenza.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'La disabilitazione disattiva la chiave senza eliminarla. La revoca (eliminazione) rimuove la chiave in modo permanente.',
    'Copy this API key now — it will never be shown again.' => 'Copia questa chiave API ora — non verrà mai più mostrata.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} memorizza solo un hash. Se perde questo valore, dovrà creare una nuova chiave.',
    'Copy this signing secret now — it will never be shown again.' => 'Copia questo segreto di firma ora — non verrà mai più mostrato.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'Il chiamante lo utilizza per firmare ogni richiesta (HMAC-SHA256). Consegnarlo insieme alla chiave API tramite un canale sicuro.',

    // Test page
    'Test API' => 'Testa API',
    'Test API Endpoints' => 'Testa endpoint API',
    'Send a request to the local API with one of your API keys, and inspect the response.' => 'Invia una richiesta all\'API locale usando una delle sue chiavi API e ispeziona la risposta.',
    'Developer resources' => 'Risorse per sviluppatori',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Scarica la raccolta e l\'ambiente Postman per testare l\'API REST di Formie fuori da Craft.',
    'Download Postman collection' => 'Scarica raccolta Postman',
    'API Key' => 'Chiave API',
    'Paste an API key to test.' => 'Incolli una chiave API da testare.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Incolli la chiave completa (fra_...). Usata solo per questo test — mai memorizzata.',
    'Signing secret' => 'Segreto di firma',
    'Leave empty if the key does not require signing.' => 'Lasci vuoto se la chiave non richiede la firma.',
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
    'fields (optional)' => 'fields (facoltativo)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Esegui test',
    'Result' => 'Risultato',
    'Status:' => 'Stato:',
    'Time:' => 'Tempo:',
    'Equivalent curl' => 'Comando curl equivalente',
    'Response headers' => 'Intestazioni della risposta',
    'Response body' => 'Corpo della risposta',
    'Running...' => 'Esecuzione in corso...',
    'Error:' => 'Errore:',
    'Unknown error' => 'Errore sconosciuto',
];
