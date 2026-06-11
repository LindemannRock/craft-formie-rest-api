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
    'Interface' => 'الواجهة',
    'Logs' => 'السجلات',
    'Test' => 'اختبار',

    // Permissions
    'Manage settings' => 'إدارة الإعدادات',
    'Manage API keys' => 'إدارة مفاتيح API',
    'Create API keys' => 'إنشاء مفاتيح API',
    'Edit API keys' => 'تحرير مفاتيح API',
    'Revoke API keys' => 'إبطال مفاتيح API',
    'View system logs' => 'عرض سجلات النظام',
    'Download system logs' => 'تنزيل سجلات النظام',

    // Common
    'Name' => 'الاسم',
    'Status' => 'الحالة',
    'Actions' => 'الإجراءات',
    'All' => 'الكل',
    'Enable' => 'تفعيل',
    'Disable' => 'تعطيل',
    'Enabled' => 'مفعّل',
    'Disabled' => 'معطّل',
    'Edit' => 'تحرير',
    'Save' => 'حفظ',
    'Save and continue editing' => 'حفظ وإكمال التحرير',
    'Set status' => 'تعيين الحالة',
    'Never' => 'أبداً',
    'Created at' => 'تم الإنشاء في',
    'Updated at' => 'تم التحديث في',

    // Controller messages
    "Couldn't save settings." => 'تعذّر حفظ الإعدادات.',
    'Settings saved.' => 'تم حفظ الإعدادات.',
    'Selected API key is not configured.' => 'مفتاح API المحدد غير مُعدّ.',
    'API key created' => 'تم إنشاء مفتاح API',
    'API key saved' => 'تم حفظ مفتاح API',
    'API key revoked' => 'تم إبطال مفتاح API',
    'Couldn’t save API key' => 'تعذر حفظ مفتاح API',
    'Couldn’t revoke API key' => 'تعذر إبطال مفتاح API',
    'API key not found' => 'مفتاح API غير موجود',
    '{count, plural, =1{1 API key revoked} other{# API keys revoked}}' => '{count, plural, zero{لم يتم إبطال أي مفاتيح API} one{تم إبطال مفتاح API واحد} two{تم إبطال مفتاحَي API} few{تم إبطال # مفاتيح API} many{تم إبطال # مفتاح API} other{تم إبطال # مفتاح API}}',
    '{count, plural, =1{1 API key enabled} other{# API keys enabled}}' => '{count, plural, zero{لم يتم تفعيل أي مفاتيح API} one{تم تفعيل مفتاح API واحد} two{تم تفعيل مفتاحَي API} few{تم تفعيل # مفاتيح API} many{تم تفعيل # مفتاح API} other{تم تفعيل # مفتاح API}}',
    '{count, plural, =1{1 API key disabled} other{# API keys disabled}}' => '{count, plural, zero{لم يتم تعطيل أي مفاتيح API} one{تم تعطيل مفتاح API واحد} two{تم تعطيل مفتاحَي API} few{تم تعطيل # مفاتيح API} many{تم تعطيل # مفتاح API} other{تم تعطيل # مفتاح API}}',
    'Couldn’t enable API keys' => 'تعذر تفعيل مفاتيح API',
    'Couldn’t disable API keys' => 'تعذر تعطيل مفاتيح API',
    'Couldn’t revoke API keys' => 'تعذر إبطال مفاتيح API',

    // Validation messages
    'Enabled keys must allow all forms or at least one specific form.' => 'يجب أن تسمح المفاتيح المفعّلة بكل النماذج أو بنموذج محدد واحد على الأقل.',
    'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).' => 'إدخال قائمة بيضاء IP غير صالح: "{entry}". استخدم عنوان IP واحدًا أو نطاق CIDR (مثل 203.0.113.5 أو 192.168.1.0/24).',

    // Settings: General
    'General Settings' => 'الإعدادات العامة',

    // Settings: Interface
    'Interface Settings' => 'إعدادات الواجهة',

    // API Keys
    'No API keys have been created yet. Create a key per consumer to control access to the REST API.' => 'لم يتم إنشاء أي مفاتيح API بعد. أنشئ مفتاحًا لكل مستهلك للتحكم في الوصول إلى REST API.',

    // Index page
    'Allowed forms' => 'النماذج المسموح بها',
    'Signing' => 'التوقيع',
    'Expires' => 'تنتهي الصلاحية',
    'Last used' => 'آخر استخدام',
    'Expired' => 'منتهي الصلاحية',
    'No API keys yet.' => 'لا توجد مفاتيح API بعد.',
    'Search API keys...' => 'البحث في مفاتيح API...',
    'API key' => 'مفتاح API',
    'API keys' => 'مفاتيح API',
    'All Forms' => 'جميع النماذج',
    'form' => 'نموذج',
    'forms' => 'نماذج',
    'No forms allowed — this key cannot be used until you add some.' => 'لا توجد نماذج مسموح بها — لا يمكن استخدام هذا المفتاح حتى تضيف بعضاً منها.',
    'Revoke' => 'إبطال',
    'Are you sure you want to revoke this API key? Any callers using it will immediately lose access.' => 'هل أنت متأكد من أنك تريد إبطال مفتاح API هذا؟ سيفقد أي مستدعين يستخدمونه الوصول فوراً.',
    'Are you sure you want to revoke 1 API key? Any callers using it will immediately lose access. This cannot be undone.' => 'هل أنت متأكد من أنك تريد إبطال مفتاح API واحد؟ سيفقد أي مستدعين يستخدمونه الوصول فوراً. لا يمكن التراجع عن هذا الإجراء.',
    'Are you sure you want to revoke {count} API keys? Any callers using them will immediately lose access. This cannot be undone.' => 'هل أنت متأكد من أنك تريد إبطال {count} مفاتيح API؟ سيفقد أي مستدعين يستخدمونها الوصول فوراً. لا يمكن التراجع عن هذا الإجراء.',
    'Prefix' => 'البادئة',
    'None' => 'لا شيء',

    // Edit page
    'New API Key' => 'مفتاح API جديد',
    'Edit API Key' => 'تحرير مفتاح API',
    'A descriptive label so you can identify this key in the list — typically the consumer it belongs to. Not exposed to callers.' => 'تسمية وصفية لتتمكن من تحديد هذا المفتاح في القائمة — عادةً المستهلك الذي ينتمي إليه. غير معروضة للمستدعين.',
    'All forms (current and future)' => 'جميع النماذج (الحالية والمستقبلية)',
    'When on, this key can read every form — including forms created after the key. When off, choose specific forms below.' => 'عند التفعيل، يمكن لهذا المفتاح قراءة كل نموذج — بما في ذلك النماذج التي تُنشأ بعد المفتاح. عند التعطيل، اختر نماذج محددة أدناه.',
    'Specific forms' => 'نماذج محددة',
    'Tick each form this key can read.' => 'حدد كل نموذج يمكن لهذا المفتاح قراءته.',
    'No forms exist yet. Create a form before this key can be useful.' => 'لا توجد نماذج بعد. أنشئ نموذجًا قبل أن يصبح هذا المفتاح مفيدًا.',
    'IP whitelist' => 'القائمة البيضاء لـ IP',
    'One entry per line. Use a single IP (<code>203.0.113.5</code>) or a CIDR range (<code>192.168.1.0/24</code>), IPv4 or IPv6. Leave empty to allow all IPs.' => 'إدخال واحد في كل سطر. استخدم عنوان IP واحدًا (<code>203.0.113.5</code>) أو نطاق CIDR (<code>192.168.1.0/24</code>)، IPv4 أو IPv6. اترك فارغًا للسماح بجميع عناوين IP.',
    'Require signing' => 'طلب التوقيع',
    'When on, every request must carry a valid HMAC-SHA256 signature computed with this key’s signing secret.' => 'عند التفعيل، يجب أن يحمل كل طلب توقيع HMAC-SHA256 صالحًا محسوبًا باستخدام سر التوقيع الخاص بهذا المفتاح.',
    'Read submissions' => 'قراءة الإرسالات',
    'When off, this key is limited to the forms endpoints and cannot read any submission data.' => 'عند التعطيل، يقتصر هذا المفتاح على نقاط نهاية النماذج ولا يمكنه قراءة أي بيانات إرسال.',
    'Rate limit' => 'حد المعدل',
    'Cap the request rate in requests per hour. Leave empty for the default (100/hour).' => 'تقييد معدل الطلبات بالطلبات في الساعة. اترك فارغًا للقيمة الافتراضية (100/ساعة).',
    'Valid until' => 'صالح حتى',
    'Optional expiry datetime. Leave empty for no expiry.' => 'تاريخ ووقت انتهاء صلاحية اختياري. اترك فارغاً لعدم وجود انتهاء صلاحية.',
    'Disabling deactivates the key without deleting it. Revoke (delete) removes the key permanently.' => 'التعطيل يوقف المفتاح دون حذفه. الإبطال (الحذف) يزيل المفتاح نهائياً.',
    'Copy this API key now — it will never be shown again.' => 'انسخ مفتاح API هذا الآن — لن يتم عرضه مرة أخرى أبداً.',
    '{pluginName} stores only a hash. If you lose this value you will need to create a new key.' => 'يخزن {pluginName} hash فقط. إذا فقدت هذه القيمة فستحتاج إلى إنشاء مفتاح جديد.',
    'Copy this signing secret now — it will never be shown again.' => 'انسخ سر التوقيع هذا الآن — لن يتم عرضه مرة أخرى أبداً.',
    'The caller uses it to sign each request (HMAC-SHA256). Deliver it together with the API key over a secure channel.' => 'يستخدمه المستدعي لتوقيع كل طلب (HMAC-SHA256). سلّمه مع مفتاح API عبر قناة آمنة.',

    // Test page
    'Test API' => 'اختبار API',
    'Test API Endpoints' => 'اختبار نقاط نهاية API',
    'Send a request to the local API using one of the configured keys, and inspect the response.' => 'أرسل طلبًا إلى API المحلية باستخدام أحد المفاتيح المكوّنة وافحص الاستجابة.',
    'Developer resources' => 'موارد المطورين',
    'Download the Postman collection and environment to test the Formie REST API outside Craft.' => 'نزّل مجموعة Postman والبيئة الخاصة بها لاختبار Formie REST API خارج Craft.',
    'Download Postman collection' => 'تنزيل مجموعة Postman',
    'No API keys configured. Set FORMIE_API_KEY (and optionally FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) in your .env, or run <code>php craft formie-rest-api/security/generate-key</code> (with DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).' => 'لا توجد مفاتيح API مُعدّة. عيّن FORMIE_API_KEY (واختياريًا FORMIE_API_KEY_LIMITED / FORMIE_API_KEY_TEST) في ملف .env، أو شغّل <code>php craft formie-rest-api/security/generate-key</code> (مع DDEV: <code>ddev craft formie-rest-api/security/generate-key</code>).',
    'API Key' => 'مفتاح API',
    'Which configured key to send.' => 'المفتاح المُعدّ المراد إرساله.',
    'Pasted key' => 'مفتاح تم لصقه',
    'Paste an API key to test.' => 'الصق مفتاح API لاختباره.',
    'Paste the full key (fra_...). Used for this test only — never stored.' => 'الصق المفتاح الكامل (fra_...). يُستخدم لهذا الاختبار فقط — ولا يتم تخزينه أبدًا.',
    'Signing secret' => 'سر التوقيع',
    'Leave empty if the key does not require signing.' => 'اتركه فارغًا إذا كان المفتاح لا يتطلب التوقيع.',
    'Endpoint' => 'نقطة النهاية',
    'Which REST endpoint to call.' => 'نقطة نهاية REST المراد استدعاؤها.',
    'ID' => 'ID',
    'Numeric form / submission ID.' => 'معرّف رقمي للنموذج أو الإرسال.',
    'Form handle' => 'معرّف النموذج',
    'Form handle (the slug, not the title).' => 'معرّف النموذج (اللاحقة، وليس العنوان).',
    'formHandle (optional)' => 'formHandle (اختياري)',
    'Filter submissions to one form.' => 'تصفية الإرسالات لنموذج واحد.',
    'dateFrom (optional)' => 'dateFrom (اختياري)',
    'dateTo (optional)' => 'dateTo (اختياري)',
    'fields (optional)' => 'fields (اختياري)',
    'limit' => 'limit',
    'offset' => 'offset',
    'Run Test' => 'تشغيل الاختبار',
    'Result' => 'النتيجة',
    'Status:' => 'الحالة:',
    'Time:' => 'الوقت:',
    'Equivalent curl' => 'أمر curl المكافئ',
    'Response headers' => 'ترويسات الاستجابة',
    'Response body' => 'محتوى الاستجابة',
    'Running...' => 'جارٍ التشغيل...',
    'Error:' => 'خطأ:',
    'Unknown error' => 'خطأ غير معروف',
];
