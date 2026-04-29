<?php
/**
 * Formie REST API translation file (Spanish)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'Nombre del plugin',
    'The public-facing name of the plugin' => 'El nombre público del plugin',

    // Navigation
    'Settings' => 'Configuración',
    'General' => 'General',
    'Test' => 'Prueba',

    // Permissions
    'Manage settings' => 'Gestionar ajustes',

    // Controller messages
    "Couldn't save settings." => 'No se pudieron guardar los ajustes.',
    'Settings saved.' => 'Ajustes guardados.',

    // Settings: General
    'General Settings' => 'Ajustes generales',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'Este valor está siendo sobrescrito por el ajuste <code>pluginName</code> en <code>config/formie-rest-api.php</code>.',

    // Test page
    'Test API' => 'Probar API',
    'Test API Endpoints' => 'Probar endpoints de la API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'Envíe una solicitud a la API local con una de las claves configuradas y revise la respuesta.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'No hay claves API configuradas. Defina FORMIE_API_KEY (y opcionalmente FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) en su archivo .env, o utilice <code>ddev craft formie-rest-api/security/generate-key</code>.',
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
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'Ejecutar prueba',
    'Result' => 'Resultado',
    'Status:' => 'Estado:',
    'Time:' => 'Tiempo:',
    'Equivalent curl' => 'Comando curl equivalente',
    'Response headers' => 'Cabeceras de respuesta',
    'Response body' => 'Cuerpo de la respuesta',
];
