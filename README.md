# Formie REST API Plugin

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-formie-rest-api.svg)](https://packagist.org/packages/lindemannrock/craft-formie-rest-api)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![Formie](https://img.shields.io/badge/Formie-3.0+-purple.svg)](https://verbb.io/craft-plugins/formie)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-formie-rest-api.svg)](LICENSE)

A REST API plugin for Craft CMS that exposes Formie forms and submissions through authenticated REST endpoints. Designed for external systems (e.g. SAP, BI tools, partner integrations) that need structured form data over HTTP.

> **Note on GraphQL:** Formie ships with its own GraphQL schema at Craft's `/api` endpoint. This plugin does **not** add GraphQL — it adds a separate REST API with its own auth (`X-API-Key`), rate limiting, and access logging. If you want GraphQL, use Formie's built-in support directly.

## License

This is a commercial plugin licensed under the [Craft License](https://craftcms.github.io/license/). It will be available on the [Craft Plugin Store](https://plugins.craftcms.com) soon. See [LICENSE.md](LICENSE.md) for details.

## ⚠️ Pre-Release

This plugin is in active development and not yet available on the Craft Plugin Store. Features and APIs may change before the initial public release.

## Requirements

- Craft CMS 5.0 or greater
- PHP 8.2 or greater
- Formie 3.0 or greater

## Overview

The Formie REST API plugin provides:
- **REST endpoints** for forms and submissions
- **API-key authentication** via `X-API-Key` header (Primary, Limited, Test)
- **Rate limiting** with `X-RateLimit-*` response headers and 429 on exceed
- **Access logging** for every request (partial key fingerprint, endpoint, IP, status)
- **CLI key generator** (`ddev craft formie-rest-api/security/generate-key`)
- **In-CP test page** for verifying keys, endpoints, and filters without leaving Craft
- **Postman collection** (in [`postman/`](postman/)) with auto-HMAC pre-request script
- **Translated CP UI** — 12 languages (EN, DE, FR, NL, ES, IT, PT, AR, JA, SV, DA, NO)

## Installation

### Via Composer

```bash
cd /path/to/project
```

```bash
composer require lindemannrock/craft-formie-rest-api
```

```bash
./craft plugin/install formie-rest-api
```

### Using DDEV

```bash
cd /path/to/project
```

```bash
ddev composer require lindemannrock/craft-formie-rest-api
```

```bash
ddev craft plugin/install formie-rest-api
```

### Via Control Panel

In the Control Panel, go to Settings → Plugins and click "Install" for Formie REST API.

## Environment Configuration

The plugin reads API keys from environment variables (or, optionally, `config/general.php`):

```bash
# Primary API key — full read/write access
FORMIE_API_KEY="..."

# Limited access key — read forms only (optional)
FORMIE_API_KEY_LIMITED="..."

# Test key — only valid when devMode=true (optional, local only)
FORMIE_API_KEY_TEST="..."
```

Use **different values per environment** (local, staging, production). Never share keys across environments.

### Generating keys

A console command generates secure keys and (optionally) writes them to `.env`:

```bash
# See available Formie REST API console commands
ddev craft formie-rest-api/help

# Focused help for the key generator
ddev craft formie-rest-api/help security/generate-key

# Native Craft/Yii signature help
ddev craft help formie-rest-api/security/generate-key
```

```bash
# Local
ddev craft formie-rest-api/security/generate-key

# Staging / production (run on the server)
php craft formie-rest-api/security/generate-key
```

The command:

1. Asks which key to generate — `primary`, `limited`, `test`, or `all` (generates all three in turn, sharing the same prefix; `test` is auto-skipped if `devMode` is off)
2. Asks for a prefix — defaults to `fra_`, type `-` for none, or supply a custom prefix
3. Prints the generated key (`prefix + 64 hex chars`)
4. Warns and confirms before replacing an existing key (avoids surprise 401s for live consumers)
5. Offers to also generate a paired HMAC **signing secret** (presence of the matching `FORMIE_API_SIGNING_SECRET[_*]` env var auto-enables required-signature mode for that key — see [HMAC Request Signing](#hmac-request-signing-optional-recommended-for-production))
6. Asks whether to write to `.env`:
   - **Yes** → writes one consolidated block per key type (`# Formie REST API — Primary` / `Limited` / `Test`, key + secret co-located). Re-running the command for the same key cleans up any legacy scattered entries it owns and replaces them with the fresh block.
   - **No** → prints the block so you can paste it into a hosting panel (Servd, Forge, Cloudways, etc.) or a secrets store

Test keys are refused (when chosen explicitly) or skipped (in `all` mode) unless `devMode=true`.

When an existing signing secret is detected, the command asks whether to **rotate** it or **keep** it — so you can rotate just the API key without invalidating signed clients.

## Authentication

### REST API Authentication
Include API key in request headers:
```bash
curl -H "X-API-Key: your-api-key-here" https://yoursite.com/api/v1/formie/forms
```

### HMAC Request Signing (optional, recommended for production)

For production keys, the plugin supports HMAC-SHA256 request signing. Signing adds:

- **Replay protection** — captured requests expire after a 5-minute timestamp window
- **Tamper detection** — the signature covers method, path, timestamp, and body
- **Defence-in-depth** — a leaked API key alone is no longer sufficient; the attacker also needs the separate signing secret

**Enable per key** by setting the matching env var (presence of the env var auto-enables the requirement on that key):

```bash
FORMIE_API_SIGNING_SECRET="..."           # paired with FORMIE_API_KEY
FORMIE_API_SIGNING_SECRET_LIMITED="..."   # paired with FORMIE_API_KEY_LIMITED
FORMIE_API_SIGNING_SECRET_TEST="..."      # paired with FORMIE_API_KEY_TEST (devMode only)
```

`ddev craft formie-rest-api/security/generate-key` offers to generate the paired signing secret at the end of the key-generation flow.

**Required headers when signing is enabled:**

| Header | Value |
|---|---|
| `X-API-Key` | The API key (as before) |
| `X-Timestamp` | Current Unix epoch seconds |
| `X-Signature` | Hex HMAC-SHA256 of the signature base, keyed by the signing secret |

**Signature base** (joined with literal `\n`):

```
METHOD\nPATH_WITH_QUERY\nTIMESTAMP\nBODY
```

For a `GET /api/v1/formie/forms` request the body is empty.

> **Sort query parameters alphabetically before signing.** When `PATH_WITH_QUERY` has a query string, sort its parameters alphabetically (byte-wise) before building the signature base — e.g. sign `/api/v1/formie/submissions?formHandle=contact&limit=10&offset=0`, not the order you happened to send them in. A CDN/proxy in front of the site (e.g. Cloudflare) normalizes query-string order before the request reaches the server, so an unsorted signature will fail with `401 Missing or invalid request signature` once a request has two or more params that aren't already in alphabetical order. The order you actually *send* the params in doesn't matter — only the order you *sign*. (The server also accepts the as-received order, so single-param and already-sorted requests work either way.)

**Example client (bash):**

```bash
KEY="fra_..."
SECRET="..."
TS=$(date +%s)
PATH_Q="/api/v1/formie/forms"
SIG=$(printf "GET\n%s\n%s\n" "$PATH_Q" "$TS" | openssl dgst -sha256 -hmac "$SECRET" -hex | awk '{print $NF}')

curl -H "X-API-Key: $KEY" \
     -H "X-Timestamp: $TS" \
     -H "X-Signature: $SIG" \
     "https://yoursite.com$PATH_Q"
```

**Example client (PHP):**

```php
$key = getenv('FORMIE_API_KEY');
$secret = getenv('FORMIE_API_SIGNING_SECRET');
$path = '/api/v1/formie/forms';
$ts = (string) time();
$sig = hash_hmac('sha256', "GET\n{$path}\n{$ts}\n", $secret);

// Send X-API-Key, X-Timestamp, X-Signature headers
```

**Example client (Node.js):**

```js
const crypto = require('crypto');

const key = process.env.FORMIE_API_KEY;
const secret = process.env.FORMIE_API_SIGNING_SECRET;

// Path must include the query string when present, e.g.
// '/api/v1/formie/submissions?formHandle=contact&limit=10'
const method = 'GET';
const pathQ = '/api/v1/formie/forms';
const body = '';                                   // empty for GET, but still signed
const ts = Math.floor(Date.now() / 1000).toString();

const base = `${method}\n${pathQ}\n${ts}\n${body}`;
const sig = crypto.createHmac('sha256', secret).update(base).digest('hex');

const res = await fetch('https://yoursite.com' + pathQ, {
    method,
    headers: {
        'X-API-Key': key,
        'X-Timestamp': ts,
        'X-Signature': sig,
        'Accept': 'application/json',
    },
});
const data = await res.json();
```

### Postman collection

A ready-to-use Postman collection lives in [`postman/`](postman/) — collection plus a single environment template. The collection-level pre-request script computes the HMAC signature automatically (sorting query params before signing) when `signing_secret` is set on the active environment, and skips it when empty (for keys without signing). Switch keys (Primary, Limited, Test) by pasting a different key into the environment. See [`postman/README.md`](postman/README.md) for setup.

You can also download the collection and environment as a ZIP from **Formie REST API → Settings → Test** in the Craft control panel.

### IP Whitelist (optional, defence-in-depth)

Restrict which client IPs can use a given API key. Useful for server-to-server integrations on stable infrastructure (SAP, ERP, internal networks).

**Enable per key** by setting the matching env var to a comma-separated list of IPs and/or CIDR ranges (presence of the env var auto-enables the restriction):

```bash
FORMIE_API_IP_WHITELIST="203.0.113.5,192.168.1.0/24,2001:db8::/32"
FORMIE_API_IP_WHITELIST_LIMITED="..."
FORMIE_API_IP_WHITELIST_TEST="..."
```

Empty/unset env → no IP restriction (request accepted from any IP).
Non-matching IP → `401 Unauthorized` with message `Request originates from an IP not allowed for this key`.

**Supports IPv4 and IPv6.** Single IPs (`203.0.113.5`, `2001:db8::1`) and CIDR ranges (`192.168.1.0/24`, `2001:db8::/32`).

> **CDN / reverse-proxy caveat:** `Craft::$app->request->getUserIP()` returns whatever sent the request to PHP. Behind a CDN or reverse proxy you'll need to configure Craft's `trustedHosts` and proxy headers correctly — otherwise the whitelist matches the proxy IP, not the real client. See [Craft's request docs](https://craftcms.com/docs/5.x/reference/config/general.html#trustedhosts) for `trustedHosts` setup.

### CORS support — not currently implemented

This plugin **does not** add `Access-Control-Allow-*` response headers and does not handle `OPTIONS` preflight requests. The API is currently designed for **server-to-server** consumers (your SAP/ERP integration, scripts, scheduled jobs) where CORS does not apply.

**This means a browser-based client on a different origin (a React/Vue dashboard, a partner widget, etc.) cannot call this API directly.** The browser will block the response with a CORS error.

If you need browser access today, the workaround is to proxy the request through your own server (your backend calls this API with `X-API-Key` and forwards the response to the browser).

CORS is on the roadmap as an opt-in, env-driven feature mirroring the HMAC and IP-whitelist patterns (per-key `FORMIE_API_ALLOWED_ORIGINS[_*]` env var). It will be implemented when the first browser consumer's requirements are known. Open an issue if you need it sooner.

### Field metadata — units & notes

Field metadata returned by `/forms/{id|handle}` reflects Formie's own settings. A few values where the unit isn't obvious:

| Field type | Setting | Unit |
|---|---|---|
| FileUpload | `sizeLimit`, `sizeMinLimit` | Megabytes (MB) |
| FileUpload | `limitFiles` | Number of files |
| Signature | `penWeight` | Pixels |
| SingleLineText / MultiLineText | `min`, `max` | See `minType` / `maxType` (`characters` or `words`) |
| Date | `minDateOffsetNumber`, `maxDateOffsetNumber` | See `minDateOffsetType` / `maxDateOffsetType` (`days`, `weeks`, `months`, `years`) |
| Date | `minYearRange`, `maxYearRange` | Years offset from current year (e.g. `-100` = 100 years ago, `100` = 100 years ahead) |

All datetime values across submission and field-value responses are ISO 8601 in the site timezone.

### Submission filtering (default behaviour)

Both production (`/api/v1/formie/submissions`) and test (`/api/test/formie/submissions`) endpoints **automatically exclude**:

- **Incomplete submissions** — abandoned drafts that were never finalised (`isIncomplete = true`)
- **Spam submissions** — anything Akismet or another captcha integration flagged (`isSpam = true`)

This is hardcoded — the API contract is "completed, non-spam form submissions". There is currently no opt-in flag to include drafts or spam. If your integration needs them, open an issue describing the use case.

## REST API Endpoints

### Versioning

The plugin uses two independent version axes — they don't move together:

| Version | Tracks | Bumps when |
|---|---|---|
| Plugin (e.g. `3.3.0`) | This package's release | Every feature/fix. Plugin major aligns to the Formie major (Formie 3.x → plugin 3.x.x; Formie 4 → plugin 4.0.0) |
| **API URL** (`/api/v1/`) | The wire-format contract consumers integrate against | Only on breaking contract changes (removed/renamed fields, changed semantics). Plugin major bumps do **not** automatically bump the API URL |

**Branch strategy.** When Formie ships a new major, `main` tracks the new major (e.g. plugin 4.x for Formie 4), and the previous Formie major lives on a maintenance branch (e.g. `3.x`) for security and critical fixes during a deprecation window. The `/api/vN/` URL stays the same across these branches as long as the wire format is unchanged — so an integrator targeting `/api/v1/` keeps working whether the site is on plugin 3.x or 4.x.

### Production Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/formie/forms` | List all forms |
| GET | `/api/v1/formie/forms/{id}` | Get form by ID |
| GET | `/api/v1/formie/forms/{handle}` | Get form by handle |
| GET | `/api/v1/formie/submissions` | List submissions |
| GET | `/api/v1/formie/submissions/{id}` | Get submission details |

### Test Endpoints (Development)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/test/formie/forms` | Test forms endpoint |
| GET | `/api/test/formie/submissions` | Test submissions endpoint |
| GET | `/api/test/formie/auth` | Test authentication |

## Example Usage

### REST API Examples

```bash
# List all forms
curl -H "X-API-Key: your-api-key" \
     https://yoursite.com/api/v1/formie/forms

# Get specific form by handle
curl -H "X-API-Key: your-api-key" \
     https://yoursite.com/api/v1/formie/forms/contactForm

# Get form submissions
curl -H "X-API-Key: your-api-key" \
     "https://yoursite.com/api/v1/formie/submissions?form=contactForm&limit=10"
```

## API Documentation

- **[postman/](postman/)** — Postman collection + environment templates with auto-HMAC pre-request script
- **[.env.example](.env.example)** — Environment variable configuration
- **In-CP Test tab** — live API tester at *Settings → Plugins → Formie REST API → Test*

## API Key Permissions

| Key Type | Permissions | Rate Limit |
|----------|-------------|------------|
| **Primary** | Read forms, Read submissions | 1000/hour |
| **Limited** | Read forms only | 100/hour |
| **Test** | Full access (dev mode only) | Unlimited |

### Two permission systems — don't confuse them

The plugin has **two separate permission systems** with different audiences. Both are called "permissions" but they don't overlap.

| | Craft user permissions | API key scopes |
|---|---|---|
| **Audience** | Logged-in CP users (humans) | External clients with `X-API-Key` |
| **Where defined** | Code → `EVENT_REGISTER_PERMISSIONS` | Code → `ApiKeyService::getValidApiKeys()` |
| **Where assigned** | CP → Settings → Users → User Groups → "Formie REST API" section | Hardcoded per env-var (Primary / Limited / Test) |
| **What's enforced** | `Manage settings` (gates the CP settings page) | `read_forms`, `read_submissions`, `create_submissions` (gates each REST endpoint) |
| **Failure status** | 403 from CP redirect / login | 403 `ForbiddenHttpException` from API |

**In practice:**
- A CP user with `Manage settings` can edit the plugin's settings page. That's all the CP grants.
- An external API consumer's access is determined entirely by **which env-var slot** their API key was generated into:
  - `FORMIE_API_KEY` → Primary scope (full read)
  - `FORMIE_API_KEY_LIMITED` → Limited scope (forms only)
  - `FORMIE_API_KEY_TEST` → Test scope (full read, devMode only)

If you give a partner a Limited key thinking they can't read submissions — they can't. The scope is enforced server-side.

## Security Features

- API key validation (per-key permission scopes: `read_forms`, `read_submissions`, `create_submissions`)
- HMAC request signing (optional, opt-in per key — replay protection, integrity, leaked-key mitigation)
- IP whitelist (optional, opt-in per key — IPv4 + IPv6 + CIDR)
- Rate limiting per key (atomic, mutex-serialized counter)
- Access logging (key, endpoint, IP, user-agent, response code)
- Development-mode restrictions (test endpoints + test key only register when `devMode = true`)
- **Not yet:** CORS for browser consumers — see [CORS support — not currently implemented](#cors-support--not-currently-implemented)

### Operational hardening (Craft-level, not plugin-level)

A couple of Craft framework settings worth knowing about — the plugin doesn't control these, but they affect API responses:

- **`X-Powered-By: Craft CMS` response header.** Craft sends this by default. Some security-conscious deployments prefer to suppress it (defence in depth — don't tell unfamiliar attackers what stack to research). Disable it in `config/general.php`:

  ```php
  return GeneralConfig::create()
      ->sendPoweredByHeader(false);
  ```

- **`Server: nginx` / similar.** Set at the web server level, not Craft. Strip via `server_tokens off;` (nginx) or equivalent.

- **`trustedHosts` / proxy headers.** If you put the API behind a CDN or reverse proxy and use the IP whitelist feature, configure Craft's `trustedHosts` correctly so `getUserIP()` returns the real client IP, not the proxy. See [Craft docs](https://craftcms.com/docs/5.x/reference/config/general.html#trustedhosts).

## Plugin Settings

Navigate to **Settings → Plugins → Formie REST API** for:
- **General** — plugin name (overridable via `config/formie-rest-api.php`)
- **Interface** — date/time display preferences inherited from LindemannRock Base.
- **Test** — live API tester: pick a configured key, choose an endpoint, set optional filters (form handle, date range, limit/offset), and view the response status, headers, body, and the equivalent `curl` command. Available regardless of `devMode`; test endpoints (`/api/test/formie/*`) only resolve when `devMode = true`.

Control Panel settings are validated and saved by active section.

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-formie-rest-api](https://github.com/LindemannRock/craft-formie-rest-api)
- **Issues**: [https://github.com/LindemannRock/craft-formie-rest-api/issues](https://github.com/LindemannRock/craft-formie-rest-api/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the [Craft License](https://craftcms.github.io/license/). See [LICENSE.md](LICENSE.md) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)

Built for use with [Formie](https://verbb.io/craft-plugins/formie) by Verbb
