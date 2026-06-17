# Configuration

Most of what you configure for Formie REST API lives on each **API key** (allowed forms, signing, IP whitelist, rate limit — see [API keys](../feature-tour/api-keys.md)), not in global settings. The plugin's own settings are small: a display name, a log level, and date/time display preferences.

Manage them in the Control Panel at **Formie REST API → Settings**, or lock them per environment with a config file. Settings persist in the plugin's own database table.

## Settings

### General

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `pluginName` | `string` | `'Formie REST API'` | The name shown in the Control Panel nav |
| `logLevel` | `string` | `'error'` | Verbosity of the plugin log: `'debug'`, `'info'`, `'warning'`, or `'error'`. `'debug'` only takes effect when Craft `devMode` is on |

### Interface (date/time display)

These control how dates and times render in the Control Panel only — they do **not** affect API responses (all API datetimes are ISO 8601). They inherit from LindemannRock Base unless overridden.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timeFormat` | `string` | base default | `'12'` (AM/PM) or `'24'` |
| `monthFormat` | `string` | base default | `'numeric'`, `'short'`, or `'long'` |
| `dateOrder` | `string` | base default | `'dmy'`, `'mdy'`, or `'ymd'` |
| `dateSeparator` | `string` | base default | `'/'`, `'-'`, or `'.'` |
| `showSeconds` | `bool` | base default | Show seconds in time display |

## Config file

Copy `vendor/lindemannrock/craft-formie-rest-api/src/config.php` to `config/formie-rest-api.php`. Values set there take precedence over the Control Panel fields, which are then shown read-only.

```php
// config/formie-rest-api.php
return [
    '*' => [
        'logLevel' => 'error',
    ],
    'dev' => [
        'logLevel' => 'debug',
    ],
];
```

> [!NOTE]
> The date/time keys override [LindemannRock Base](https://github.com/LindemannRock/craft-plugin-base) defaults for this plugin only. To change them across every LindemannRock plugin, set them once in `config/lindemannrock-base.php` instead.

## Not configured here

| Concern | Where |
|---------|-------|
| Per-key allowed forms, signing, IP whitelist, rate limit, expiry | [API keys](../feature-tour/api-keys.md) |
| Rate-limit kill switch (`FORMIE_API_RATE_LIMIT_DISABLED`) | [Rate limiting](../developers/rate-limiting.md) |
