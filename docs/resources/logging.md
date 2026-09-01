# Logging

Formie REST API writes per-day log files through the bundled [Logging Library](https://github.com/LindemannRock/craft-logging-library).

> [!NOTE]
> Logging Library is required by Composer. Install or activate it in Craft to enable log viewing.

```bash title="PHP"
php craft plugin/install logging-library
```

```bash title="DDEV"
ddev craft plugin/install logging-library
```

Or via the Control Panel: **Settings → Plugins → Logging Library → Install**

Use these logs to investigate API request outcomes, API-key changes, rate-limit safeguards, and settings or persistence failures.

## Log levels

Four log levels are available, in order of verbosity:

| Level | What is logged |
|-------|----------------|
| `error` | Critical errors only |
| `warning` | Errors and warnings |
| `info` | General informational messages |
| `debug` | All lower levels; Formie REST API currently adds no separate debug-only event family |

Each level includes all messages from the levels above it. `error` is the least verbose; `debug` is the most.

> [!WARNING]
> Debug level requires Craft's `devMode` to be enabled. If `logLevel` is set to `debug` while `devMode` is disabled, Formie REST API falls back to `info` and records a warning. Use `debug` for local development or short diagnostic sessions.

## Configuration

The default level is `error`. Set `logLevel` in `config/formie-rest-api.php` to change it:

```php
// config/formie-rest-api.php
return [
    'logLevel' => 'error', // 'error', 'warning', 'info', or 'debug'
];
```

For environment-specific logging, keep production quieter and enable debug only where Craft's `devMode` is enabled:

```php
// config/formie-rest-api.php
return [
    '*' => [
        'logLevel' => 'error',
    ],
    'production' => [
        'logLevel' => 'error',
    ],
    'staging' => [
        'logLevel' => 'warning',
    ],
    'dev' => [
        'logLevel' => 'debug',
    ],
];
```

See [Configuration](../get-started/configuration.md) for config-file setup and precedence.

## Log file location

```text
storage/logs/formie-rest-api-YYYY-MM-DD.log
```

The files rotate daily. Each line includes its timestamp, severity, category, message, and any JSON-encoded context supplied by Formie REST API.

## Viewing logs in the CP

When Logging Library is installed and enabled, **Formie REST API → Logs** lets you inspect and download these files without leaving the Control Panel.

From there you can:

- Browse log entries by day
- Filter by log level
- Search messages and context
- View file sizes and entry counts
- Download individual log files for external analysis

The `formieRestApi:viewSystemLogs` permission is required to access the Logs section. Its nested `formieRestApi:downloadSystemLogs` permission is required to download files. Formie REST API does not register a separate `formieRestApi:viewLogs` parent permission.

## What gets logged

The level of detail depends on your configured `logLevel`.

### Error (`error`)

- Settings database-load failures
- API-key validation failures
- API-key save and deletion failures

### Warning (`warning`)

- Rate-limit mutex or cache failures that make rate limiting fail open
- Failed API-key usage-timestamp updates
- Debug fallback when `logLevel` is set to `debug` without `devMode`

### Info (`info`)

- Every finalized production or devMode API request, including failed authentication and the final HTTP status
- API keys saved or deleted
- Bulk enabled-state changes and bulk revocations

### Debug (`debug`)

- Formie REST API currently emits no plugin-specific debug-only events; selecting this level includes the `info`, `warning`, and `error` events above

## API request context and privacy

Each API access entry includes:

- A truncated key fingerprint (the first 10 characters followed by an ellipsis), or `(missing)` when no key was supplied
- The requested endpoint and HTTP method
- The client IP address and user agent
- Query parameters with top-level `password`, `token`, and `secret` values removed from the separate parameter context
- The final HTTP response status

The full plaintext API key and HMAC signing secret are not written to the log. The endpoint field contains the request URL, including its query string, so keep credentials and other secrets in headers rather than URL parameters. Client IPs and user-agent strings can be sensitive operational data; grant log permissions narrowly and handle downloaded files accordingly.

## Permissions

| Action | Permission |
|--------|------------|
| Access the Logs section in the CP | `formieRestApi:viewSystemLogs` |
| Download log files | `formieRestApi:downloadSystemLogs` |

See [Permissions](../developers/permissions.md) for the full permission hierarchy.
