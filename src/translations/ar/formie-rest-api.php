<?php
/**
 * Formie REST API translation file (Arabic)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Plugin Name' => 'اسم الإضافة',
    'The public-facing name of the plugin' => 'الاسم العام للإضافة',

    // Navigation
    'Settings' => 'الإعدادات',
    'General' => 'عام',
    'Test' => 'اختبار',

    // Permissions
    'Manage settings' => 'إدارة الإعدادات',

    // Controller messages
    "Couldn't save settings." => 'تعذّر حفظ الإعدادات.',
    'Settings saved.' => 'تم حفظ الإعدادات.',

    // Settings: General
    'General Settings' => 'الإعدادات العامة',
    'This is being overridden by the <code>pluginName</code> setting in <code>config/formie-rest-api.php</code>.' => 'يتم تجاوز هذه القيمة بواسطة إعداد <code>pluginName</code> في <code>config/formie-rest-api.php</code>.',

    // Test page
    'Test API' => 'اختبار API',
    'Test API Endpoints' => 'اختبار نقاط نهاية API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'أرسِل طلبًا إلى API المحلي باستخدام أحد المفاتيح المُعدّة، وافحص الاستجابة.',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or use <code>ddev craft formie-rest-api/security/generate-key</code>.' => 'لا توجد مفاتيح API مُعدّة. عيّن FORMIE_API_KEY (واختياريًا FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) في ملف .env، أو استخدم <code>ddev craft formie-rest-api/security/generate-key</code>.',
    'API Key' => 'مفتاح API',
    'Which configured key to send.' => 'المفتاح المُعدّ المراد إرساله.',
    'Endpoint' => 'نقطة النهاية',
    'Which REST endpoint to call.' => 'نقطة نهاية REST المراد استدعاؤها.',
    'ID' => 'المعرّف',
    'Numeric form / submission ID.' => 'معرّف رقمي للنموذج أو الإرسال.',
    'Form handle' => 'معرّف النموذج',
    'Form handle (the slug, not the title).' => 'معرّف النموذج (اللاحقة، وليس العنوان).',
    'formHandle (optional)' => 'formHandle (اختياري)',
    'Filter submissions to one form.' => 'تصفية الإرسالات لنموذج واحد.',
    'dateFrom (optional)' => 'dateFrom (اختياري)',
    'dateTo (optional)' => 'dateTo (اختياري)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'تشغيل الاختبار',
    'Result' => 'النتيجة',
    'Status:' => 'الحالة:',
    'Time:' => 'الوقت:',
    'Equivalent curl' => 'أمر curl المكافئ',
    'Response headers' => 'ترويسات الاستجابة',
    'Response body' => 'محتوى الاستجابة',
];
