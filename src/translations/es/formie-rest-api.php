<?php
/**
 * Formie REST API translation file (Spanish)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'Gestione claves API, asegure los endpoints y pruebe las respuestas de datos de Formie desde el área de ajustes del plugin.',
    'Open Formie REST API' => 'Abrir Formie REST API',
    // Navigation
    'Settings' => 'Configuración',
    'Plugins' => 'Plugins',
    'General' => 'General',
    'Interface' => 'Interfaz',
    'Test' => 'Prueba',

    // Permissions
    'Manage settings' => 'Gestionar ajustes',

    // Controller messages
    "Couldn't save settings." => 'No se pudieron guardar los ajustes.',
    'Settings saved.' => 'Configuración guardada.',
    'Selected API key is not configured.' => 'La clave API seleccionada no está configurada.',

    // Settings: General
    'General Settings' => 'Ajustes generales',

    // Settings: Interface
    'Interface Settings' => 'Configuración de interfaz',

    // Settings: Configuration warning
    'COPIED' => 'COPIADO',
    'COPY' => 'COPIAR',
    'Configuration Required' => 'Configuración requerida',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'Genere claves separadas por entorno — nunca copie claves de producción a staging o desarrollo.',
    'No API keys configured.' => 'No hay claves API configuradas.',
    'Run one of these commands in your terminal:' => 'Ejecute uno de estos comandos en su terminal:',
    'Standard:' => 'Estándar:',
    'The plugin will reject every request until at least one key is set.' => 'El plugin rechazará todas las solicitudes hasta que se configure al menos una clave.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'Esto escribirá {keys} y los secretos de firma correspondientes en su archivo {file}.',
    'Warning:' => 'Advertencia:',
    'error' => 'error',

    // Test page
    'Test API' => 'Probar API',
    'Test API Endpoints' => 'Probar endpoints de la API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Envíe una solicitud a la API local con una de las claves configuradas e inspeccione la respuesta.',
    'Developer resources' => 'Recursos para desarrolladores',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'Descargue la colección y el entorno de Postman para probar la API REST de Formie fuera de Craft.',
    'Download Postman collection' => 'Descargar colección de Postman',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'No hay claves API configuradas. Defina FORMIE_API_KEY (y opcionalmente FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) en su archivo .env, o ejecute <code>php craft formie-rest-api/security/generate-key</code> (con DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'Clave API',
    'Which configured key to send.' => 'Qué clave configurada enviar.',
    'Endpoint' => 'Endpoint',
    'Which REST endpoint to call.' => 'Qué endpoint REST llamar.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'ID numérico del formulario o envío.',
    'Form handle' => 'Handle del formulario',
    'Form handle (the slug, not the title).' => 'Handle del formulario (el slug, no el título).',
    'formHandle (optional)' => 'formHandle (opcional)',
    'Filter submissions to one form.' => 'Filtrar los envíos a un solo formulario.',
    'dateFrom (optional)' => 'dateFrom (opcional)',
    'dateTo (optional)' => 'dateTo (opcional)',
    'fields (optional)' => 'fields (opcional)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Ejecutar prueba',
    'Result' => 'Resultado',
    'Status:' => 'Estado:',
    'Time:' => 'Tiempo:',
    'Equivalent curl' => 'Comando curl equivalente',
    'Response headers' => 'Cabeceras de respuesta',
    'Response body' => 'Cuerpo de la respuesta',
    'Running…' => 'Ejecutando…',
    'Error:' => 'Error:',
    'Unknown error' => 'Error desconocido',
];
