<?php
/**
 * Formie REST API translation file (Portuguese)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'Nome do plugin',
    'The public-facing name of the plugin' => 'O nome público do plugin',

    // Navigation
    'Settings' => 'Definições',
    'General' => 'Geral',
    'Test' => 'Teste',

    // Permissions
    'Manage settings' => 'Gerir definições',

    // Controller messages
    "Couldn't save settings." => 'Não foi possível guardar as definições.',
    'Settings saved.' => 'Definições guardadas.',

    // Settings: General
    'General Settings' => 'Definições gerais',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Este valor está a ser substituído pela definição <code>pluginName</code> em <code>config/formie-rest-api.php</code>.',

    // Test page
    'Test API' => 'Testar API',
    'Test API Endpoints' => 'Testar endpoints da API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Envie um pedido à API local utilizando uma das chaves configuradas e analise a resposta.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'Nenhuma chave API configurada. Defina FORMIE_API_KEY (e opcionalmente FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) no seu ficheiro .env, ou utilize <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'Chave API',
    'Which configured key to send.' => 'Que chave configurada enviar.',
    'Endpoint' => 'Endpoint',
    'Which REST endpoint to call.' => 'Que endpoint REST chamar.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'ID numérico do formulário ou submissão.',
    'Form handle' => 'Handle do formulário',
    'Form handle (the slug, not the title).' => 'Handle do formulário (o slug, não o título).',
    'formHandle (optional)' => 'formHandle (opcional)',
    'Filter submissions to one form.' => 'Filtrar as submissões a um único formulário.',
    'dateFrom (optional)' => 'dateFrom (opcional)',
    'dateTo (optional)' => 'dateTo (opcional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Executar teste',
    'Result' => 'Resultado',
    'Status:' => 'Estado:',
    'Time:' => 'Tempo:',
    'Equivalent curl' => 'Comando curl equivalente',
    'Response headers' => 'Cabeçalhos da resposta',
    'Response body' => 'Corpo da resposta',
];
