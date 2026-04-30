<?php
/**
 * Formie REST API translation file (French)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'Nom du plugin',
    'The public-facing name of the plugin' => 'Le nom public du plugin',

    // Navigation
    'Settings' => 'Paramètres',
    'General' => 'Général',
    'Test' => 'Test',

    // Permissions
    'Manage settings' => 'Gérer les paramètres',

    // Controller messages
    "Couldn't save settings." => 'Impossible d\'enregistrer les paramètres.',
    'Settings saved.' => 'Paramètres enregistrés.',

    // Settings: General
    'General Settings' => 'Paramètres généraux',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Cette valeur est remplacée par le paramètre <code>pluginName</code> dans <code>config/formie-rest-api.php</code>.',

    // Settings: Configuration warning
    'COPIED' => 'COPIÉ',
    'COPY' => 'COPIER',
    'Configuration Required' => 'Configuration requise',
    'DDEV:' => 'DDEV :',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Générez des clés distinctes par environnement — ne copiez jamais les clés de production vers le staging ou le développement.',
    'No API keys configured.' => 'Aucune clé API configurée.',
    'Run one of these commands in your terminal:' => 'Exécutez l\'une de ces commandes dans votre terminal :',
    'Standard:' => 'Standard :',
    'The plugin will reject every request until at least one key is set.' => 'Le plugin rejettera toutes les requêtes tant qu\'au moins une clé n\'est pas définie.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Cela écrira {keys} et les secrets de signature correspondants dans votre fichier {file}.',
    'Warning:' => 'Avertissement :',
    'error' => 'erreur',

    // Test page
    'Test API' => 'Tester l\'API',
    'Test API Endpoints' => 'Tester les points de terminaison API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Envoyez une requête à l\'API locale avec l\'une des clés configurées et examinez la réponse.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Aucune clé API configurée. Définissez FORMIE_API_KEY (et éventuellement FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) dans votre fichier .env, ou utilisez <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'Clé API',
    'Which configured key to send.' => 'Quelle clé configurée envoyer.',
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
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Lancer le test',
    'Result' => 'Résultat',
    'Status:' => 'Statut :',
    'Time:' => 'Durée :',
    'Equivalent curl' => 'Commande curl équivalente',
    'Response headers' => 'En-têtes de réponse',
    'Response body' => 'Corps de la réponse',
];
