<?php
/**
 * Formie REST API translation file (Arabic)
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [
    // Plugin meta
    'Formie REST API' => 'Formie REST API',
    'Manage API keys, secure endpoints, and test Formie data responses from the plugin settings area.' => 'أدر مفاتيح API، وأمّن نقاط النهاية، واختبر استجابات بيانات Formie من منطقة إعدادات الإضافة.',
    'Open Formie REST API' => 'فتح Formie REST API',
    // Navigation
    'Settings' => 'الإعدادات',
    'Plugins' => 'الإضافات',
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

    // Settings: Configuration warning
    'COPIED' => 'تم النسخ',
    'COPY' => 'نسخ',
    'Configuration Required' => 'الإعداد مطلوب',
    'DDEV:' => 'DDEV:',
    'Generate separate keys per environment — never copy production keys to staging or development.' => 'أنشئ مفاتيح منفصلة لكل بيئة — لا تنسخ أبدًا مفاتيح الإنتاج إلى بيئة التجريب أو التطوير.',
    'No API keys configured.' => 'لم يتم إعداد أي مفاتيح API.',
    'Run one of these commands in your terminal:' => 'شغّل أحد هذه الأوامر في الطرفية:',
    'Standard:' => 'قياسي:',
    'The plugin will reject every request until at least one key is set.' => 'سترفض الإضافة كل طلب حتى يتم ضبط مفتاح واحد على الأقل.',
    'This will write {keys} and matching signing secrets to your {file} file.' => 'سيكتب هذا {keys} والأسرار المطابقة للتوقيع في ملف {file} الخاص بك.',
    'Warning:' => 'تحذير:',
    'error' => 'خطأ',

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
