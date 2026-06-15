# Translations

Formie REST API's Control Panel UI is translated into 12 languages out of the box. (API responses themselves are data, not translated.)

## Supported languages

| Language | Code |
|----------|------|
| English | `en` |
| German | `de` |
| French | `fr` |
| Dutch | `nl` |
| Spanish | `es` |
| Arabic | `ar` |
| Italian | `it` |
| Portuguese | `pt` |
| Japanese | `ja` |
| Swedish | `sv` |
| Danish | `da` |
| Norwegian | `no` |

Translations are applied automatically based on the user's preferred language in Craft's Control Panel settings.

## Overriding translations

Override any string by creating a static translation file in your project:

```
translations/
└── de/
    └── formie-rest-api.php
```

```php
<?php

return [
    'API Keys' => 'API-Schlüssel',  // Override the default
];
```

Only the keys you include are replaced — everything else uses the plugin's built-in translations.

See [Craft's Static Translation Strings](https://craftcms.com/docs/5.x/system/sites.html#static-message-translations) for details.

## Contributing translations

Found a translation issue? [Open an issue](https://github.com/LindemannRock/craft-formie-rest-api/issues) with the language, the current string, and your suggested correction.
