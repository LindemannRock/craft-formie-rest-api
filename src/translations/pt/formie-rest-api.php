<?php
/**
 * Formie REST API translation file (Portuguese)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Faça a gestão de chaves API, proteja endpoints e teste respostas de dados Formie a partir da área de definições do plugin.',
    'Open Formie REST API' => 'Abrir Formie REST API',
    // Navigation
    'Settings' => 'Definições',
    'Plugins' => 'Plugins',
    'General' => 'Geral',
    'Interface' => 'Interface',
    'Logs' => 'Registos',
    'Test' => 'Teste',

    // Permissions
    'Manage settings' => 'Gerir definições',
    'Manage API keys' => 'Gerir chaves API',
    'Create API keys' => 'Criar chaves API',
    'Edit API keys' => 'Editar chaves API',
    'Revoke API keys' => 'Revogar chaves API',
    'View system logs' => 'Ver registos do sistema',
    'Download system logs' => 'Descarregar registos do sistema',

    // Common
    'Name' => 'Nome',
    'Status' => 'Estado',
    'Actions' => 'Ações',
    'All' => 'Todos',
    'Enable' => 'Ativar',
    'Disable' => 'Desativar',
    'Enabled' => 'Ativado',
    'Disabled' => 'Desativado',
    'Edit' => 'Editar',
    'Save' => 'Guardar',
    'Save and continue editing' => 'Guardar e continuar a editar',
    'Set status' => 'Definir estado',
    'Never' => 'Nunca',
    'Created at' => 'Criado a',
    'Updated at' => 'Atualizado a',

    // Controller messages
    "Couldn't save settings." => 'Não foi possível guardar as definições.',
    'Settings saved.' => 'Definições guardadas.',
    'Selected API key is not configured.' => 'A chave API selecionada não está configurada.',
    'API key created' => 'Chave API criada',
    'API key saved' => 'Chave API guardada',
    'API key revoked' => 'Chave API revogada',
    'Couldn’t save API key' => 'Não foi possível guardar a chave API',
    'Couldn’t revoke API key' => 'Não foi possível revogar a chave API',
    'API key not found' => 'Chave API não encontrada',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, =1{1 chave API revogada} other{# chaves API revogadas}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, =1{1 chave API ativada} other{# chaves API ativadas}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, =1{1 chave API desativada} other{# chaves API desativadas}}',
    'Couldn’t enable API keys' => 'Não foi possível ativar as chaves API',
    'Couldn’t disable API keys' => 'Não foi possível desativar as chaves API',
    'Couldn’t revoke API keys' => 'Não foi possível revogar as chaves API',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'As chaves ativadas têm de permitir todos os formulários ou pelo menos um formulário específico.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'Entrada de lista branca de IP inválida: "{entry}". Utilize um endereço IP único ou um intervalo CIDR (ex. 203.0.113.5 ou 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'Definições gerais',

    // Settings: Interface
    'Interface Settings' => 'Definições de interface',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'Ainda não foram criadas chaves API. Crie uma chave por consumidor para controlar o acesso à REST API.',

    // Index page
    'Allowed forms' => 'Formulários permitidos',
    'Signing' => 'Assinatura',
    'Expires' => 'Expira',
    'Last used' => 'Última utilização',
    'Expired' => 'Expirado',
    'No API keys yet.' => 'Ainda sem chaves API.',
    'Search API keys...' => 'Pesquisar chaves API...',
    'API key' => 'chave API',
    'API keys' => 'chaves API',
    'All Forms' => 'Todos os formulários',
    'form' => 'formulário',
    'forms' => 'formulários',
    'No forms allowed — this key cannot be used until you add some.' => 'Nenhum formulário permitido — esta chave não pode ser utilizada até adicionar alguns.',
    'Revoke' => 'Revogar',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'Revogar esta chave API? Quaisquer chamadores que a utilizem perderão imediatamente o acesso.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'Revogar 1 chave API? Quaisquer chamadores que a utilizem perderão imediatamente o acesso. Esta ação não pode ser anulada.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'Revogar {count} chaves API? Quaisquer chamadores que as utilizem perderão imediatamente o acesso. Esta ação não pode ser anulada.',
    'Prefix' => 'Prefixo',
    'None' => 'Nenhum',

    // Edit page
    'New API Key' => 'Nova chave API',
    'Edit API Key' => 'Editar chave API',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'Uma etiqueta descritiva para poder identificar esta chave na lista — normalmente o consumidor a que pertence. Não é exposta aos chamadores.',
    'All forms (current and future)' => 'Todos os formulários (atuais e futuros)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'Quando ativado, esta chave pode ler todos os formulários — incluindo formulários criados após a chave. Quando desativado, escolha formulários específicos abaixo.',
    'Specific forms' => 'Formulários específicos',
    'Tick each form this key can read.' => 'Marque cada formulário que esta chave pode ler.',
    'No forms exist yet. Create a form before this key can be useful.' => 'Ainda não existem formulários. Crie um formulário antes de esta chave poder ser útil.',
    'IP whitelist' => 'Lista branca de IP',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'Uma entrada por linha. Utilize um IP único (<code>203.0.113.5</code>) ou um intervalo CIDR (<code>192.168.1.0/24</code>), IPv4 ou IPv6. Deixe vazio para permitir todos os IP.',
    'Require signing' => 'Exigir assinatura',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'Quando ativado, cada pedido deve incluir uma assinatura HMAC-SHA256 válida calculada com o segredo de assinatura desta chave.',
    'Read submissions' => 'Ler submissões',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'Quando desativado, esta chave está limitada aos endpoints de formulários e não pode ler dados de submissão.',
    'Rate limit' => 'Limite de taxa',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'Limite a taxa de pedidos em pedidos por hora. Deixe vazio para o valor predefinido (100/hora).',
    'Valid until' => 'Válida até',
    'Optional expiry datetime. Leave empty for no expiry.' => 'Data/hora de expiração opcional. Deixe vazio para sem expiração.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'Desativar desativa a chave sem a eliminar. Revogar (eliminar) remove a chave permanentemente.',
    'Copy this API key now — it will never be shown again.' => 'Copie esta chave API agora — não voltará a ser apresentada.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => 'O {pluginName} armazena apenas um hash. Se perder este valor terá de criar uma nova chave.',
    'Copy this signing secret now — it will never be shown again.' => 'Copie este segredo de assinatura agora — não voltará a ser apresentado.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'O chamador utiliza-o para assinar cada pedido (HMAC-SHA256). Entregue-o juntamente com a chave API através de um canal seguro.',

    // Test page
    'Test API' => 'Testar API',
    'Test API Endpoints' => 'Testar endpoints da API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Envie um pedido para a API local utilizando uma das chaves configuradas e inspecione a resposta.',
    'Developer resources' => 'Recursos para programadores',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Descarregue a coleção e o ambiente Postman para testar a API REST do Formie fora do Craft.',
    'Download Postman collection' => 'Descarregar coleção Postman',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'Nenhuma chave API configurada. Defina FORMIE_API_KEY (e opcionalmente FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) no seu ficheiro .env, ou execute <code>php craft formie-rest-api/security/generate-key</code> (com DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'Chave de API',
    'Which configured key to send.' => 'Que chave configurada enviar.',
    'Pasted key' => 'Chave colada',
    'Paste an API key to test.' => 'Cole uma chave API para testar.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'Cole a chave completa (fra_...). Usada apenas para este teste — nunca armazenada.',
    'Signing secret' => 'Segredo de assinatura',
    'Leave empty if the key does not require signing.' => 'Deixe vazio se a chave não exigir assinatura.',
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
    'fields (optional)' => 'fields (opcional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Executar teste',
    'Result' => 'Resultado',
    'Status:' => 'Estado:',
    'Time:' => 'Tempo:',
    'Equivalent curl' => 'Comando curl equivalente',
    'Response headers' => 'Cabeçalhos da resposta',
    'Response body' => 'Corpo da resposta',
    'Running...' => 'A executar...',
    'Error:' => 'Erro:',
    'Unknown error' => 'Erro desconhecido',
];
