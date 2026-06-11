<?php
/**
 * Formie REST API translation file (French)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Gérez les clés API, sécurisez les points de terminaison et testez les réponses de données Formie depuis la zone des paramètres du plugin.',
    'Open Formie REST API' => 'Ouvrir Formie REST API',
    // Navigation
    'Settings' => 'Paramètres',
    'Plugins' => 'Plugins',
    'General' => 'Général',
    'Interface' => 'Interface',
    'Logs' => 'Journaux',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Gérer les paramètres',
    'Manage API keys' => 'Gérer les clés API',
    'Create API keys' => 'Créer des clés API',
    'Edit API keys' => 'Modifier les clés API',
    'Revoke API keys' => 'Révoquer les clés API',
    'View system logs' => 'Afficher les journaux système',
    'Download system logs' => 'Télécharger les journaux système',

    // Common
    'Name' => 'Nom',
    'Status' => 'Statut',
    'Actions' => 'Actions',
    'All' => 'Tous',
    'Enable' => 'Activer',
    'Disable' => 'Désactiver',
    'Enabled' => 'Activé',
    'Disabled' => 'Désactivé',
    'Edit' => 'Modifier',
    'Save' => 'Enregistrer',
    'Save and continue editing' => 'Enregistrer et continuer l\'édition',
    'Set status' => 'Définir le statut',
    'Never' => 'Jamais',
    'Created at' => 'Créé à',
    'Updated at' => 'Mis à jour à',

    // Controller messages
    "Couldn't save settings." => 'Impossible d\'enregistrer les paramètres.',
    'Settings saved.' => 'Paramètres enregistrés.',
    'Selected API key is not configured.' => 'La clé API sélectionnée n\'est pas configurée.',
    'API key created' => 'Clé API créée',
    'API key saved' => 'Clé API enregistrée',
    'API key revoked' => 'Clé API révoquée',
    'Couldn’t save API key' => 'Impossible d\'enregistrer la clé API',
    'Couldn’t revoke API key' => 'Impossible de révoquer la clé API',
    'API key not found' => 'Clé API introuvable',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 clé API révoquée} other{# clés API révoquées}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 clé API activée} other{# clés API activées}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 clé API désactivée} other{# clés API désactivées}}',
    'Couldn’t enable API keys' => 'Impossible d\'activer les clés API',
    'Couldn’t disable API keys' => 'Impossible de désactiver les clés API',
    'Couldn’t revoke API keys' => 'Impossible de révoquer les clés API',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'Les clés activées doivent autoriser tous les formulaires ou au moins un formulaire spécifique.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Entrée de liste blanche IP invalide : « {entry} ». Utilisez une adresse IP unique ou une plage CIDR (ex. 203.0.113.5 ou 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Paramètres généraux',

    // Settings: Interface
    'Interface Settings' => 'Paramètres d\'interface',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Aucune clé API n\'a encore été créée. Créez une clé par consommateur pour contrôler l\'accès à la REST API.',

    // Index page
    'Allowed forms' => 'Formulaires autorisés',
    'Signing' => 'Signature',
    'Expires' => 'Expire',
    'Last used' => 'Dernière utilisation',
    'Expired' => 'Expiré',
    'No API keys yet.' => 'Aucune clé API pour l\'instant.',
    'Search API keys...' => 'Rechercher des clés API...',
    'API key' => 'clé API',
    'API keys' => 'clés API',
    'All Forms' => 'Tous les formulaires',
    'form' => 'formulaire',
    'forms' => 'formulaires',
    'No forms allowed — this key cannot be used until you add some.' => 'Aucun formulaire autorisé — cette clé ne peut pas être utilisée tant que vous n\'en ajoutez pas.',
    'Revoke' => 'Révoquer',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Êtes-vous sûr de vouloir révoquer cette clé API ? Tous les appelants qui l\'utilisent perdront immédiatement leur accès.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Êtes-vous sûr de vouloir révoquer 1 clé API ? Tous les appelants qui l\'utilisent perdront immédiatement leur accès. Cette action est irréversible.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Êtes-vous sûr de vouloir révoquer {count} clés API ? Tous les appelants qui les utilisent perdront immédiatement leur accès. Cette action est irréversible.',
    'Prefix' => 'Préfixe',
    'None' => 'Aucun',

    // Edit page
    'New API Key' => 'Nouvelle clé API',
    'Edit API Key' => 'Modifier la clé API',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'Un libellé descriptif pour identifier cette clé dans la liste — généralement le consommateur auquel elle appartient. Non exposé aux appelants.',
    'All forms (current and future)' => 'Tous les formulaires (actuels et futurs)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Lorsqu\'activé, cette clé peut lire chaque formulaire — y compris les formulaires créés après la clé. Lorsque désactivé, choisissez des formulaires spécifiques ci-dessous.',
    'Specific forms' => 'Formulaires spécifiques',
    'Tick each form this key can read.' => 'Cochez chaque formulaire que cette clé peut lire.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Aucun formulaire n\'existe encore. Créez un formulaire avant que cette clé puisse être utile.',
    'IP whitelist' => 'Liste blanche IP',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Une entrée par ligne. Utilisez une adresse IP unique (<code>203.0.113.5</code>) ou une plage CIDR (<code>192.168.1.0/24</code>), IPv4 ou IPv6. Laissez vide pour autoriser toutes les IP.',
    'Require signing' => 'Exiger la signature',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Lorsqu\'activé, chaque requête doit porter une signature HMAC-SHA256 valide calculée avec le secret de signature de cette clé.',
    'Read submissions' => 'Lire les soumissions',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Lorsque désactivé, cette clé est limitée aux points de terminaison des formulaires et ne peut lire aucune donnée de soumission.',
    'Rate limit' => 'Limite de débit',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Plafonner le débit de requêtes en requêtes par heure. Laissez vide pour la valeur par défaut (100/heure).',
    'Valid until' => 'Valide jusqu\'au',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Date et heure d\'expiration optionnelles. Laissez vide pour aucune expiration.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'La désactivation désactive la clé sans la supprimer. Révoquer (supprimer) supprime la clé définitivement.',
    'Copy this API key now — it will never be shown again.' => 'Copiez cette clé API maintenant — elle ne sera plus jamais affichée.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => '{pluginName} ne stocke qu\'un hash. Si vous perdez cette valeur, vous devrez créer une nouvelle clé.',
    'Copy this signing secret now — it will never be shown again.' => 'Copiez ce secret de signature maintenant — il ne sera plus jamais affiché.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'L\'appelant l\'utilise pour signer chaque requête (HMAC-SHA256). Transmettez-le avec la clé API via un canal sécurisé.',

    // Test page
    'Test API' => 'Tester l\'API',
    'Test API Endpoints' => 'Tester les points de terminaison API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Envoyez une requête à l\'API locale avec l\'une des clés configurées, puis inspectez la réponse.',
    'Developer resources' => 'Ressources développeur',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Téléchargez la collection et l\'environnement Postman pour tester l\'API REST Formie en dehors de Craft.',
    'Download Postman collection' => 'Télécharger la collection Postman',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'Aucune clé API configurée. Définissez FORMIE_API_KEY (et éventuellement FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) dans votre fichier .env, ou exécutez <code>php craft formie-rest-api/security/generate-key</code> (avec DDEV : <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'Clé API',
    'Which configured key to send.' => 'Quelle clé configurée envoyer.',
    'Pasted key' => 'Clé collée',
    'Paste an API key to test.' => 'Collez une clé API à tester.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Collez la clé complète (fra_...). Utilisée uniquement pour ce test — jamais enregistrée.',
    'Signing secret' => 'Secret de signature',
    'Leave empty if the key does not require signing.' => 'Laissez vide si la clé ne nécessite pas de signature.',
    'Endpoint' => 'Point de terminaison',
    'Which REST endpoint to call.' => 'Quel point de terminaison REST appeler.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'ID numérique du formulaire ou de la soumission.',
    'Form handle' => 'Handle du formulaire',
    'Form handle (the slug, not the title).' => 'Handle du formulaire (le slug, pas le titre).',
    'formHandle (optional)' => 'formHandle (facultatif)',
    'Filter submissions to one form.' => 'Filtrer les soumissions à un seul formulaire.',
    'dateFrom (optional)' => 'dateFrom (facultatif)',
    'dateTo (optional)' => 'dateTo (facultatif)',
    'fields (optional)' => 'fields (facultatif)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Lancer le test',
    'Result' => 'Résultat',
    'Status:' => 'Statut :',
    'Time:' => 'Durée :',
    'Equivalent curl' => 'Commande curl équivalente',
    'Response headers' => 'En-têtes de réponse',
    'Response body' => 'Corps de la réponse',
    'Running…' => 'Exécution en cours…',
    'Error:' => 'Erreur :',
    'Unknown error' => 'Erreur inconnue',
];
