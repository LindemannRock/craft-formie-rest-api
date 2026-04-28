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
# Local
ddev craft formie-rest-api/security/generate-key

# Staging / production (run on the server)
php craft formie-rest-api/security/generate-key
```

The command:

1. Asks which key to generate (Primary / Limited / Test)
2. Asks for a prefix — defaults to `fra_`, type `-` for none, or supply a custom prefix
3. Prints the generated key (`prefix + 64 hex chars`)
4. Asks whether to write to `.env`:
   - **Yes** → adds or replaces the variable in the local `.env` (with a confirmation prompt before overwriting an existing key)
   - **No** → leaves the file untouched so you can paste the key into a hosting panel (Servd, Forge, Cloudways, etc.) or a secrets store

Test keys are refused unless `devMode=true`.

## Authentication

### REST API Authentication
Include API key in request headers:
```bash
curl -H "X-API-Key: your-api-key-here" https://yoursite.com/api/v1/formie/forms
```

## REST API Endpoints

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

- **[API_TEST_GUIDE.md](API_TEST_GUIDE.md)** - Complete REST API testing guide
- **[.env.example](.env.example)** - Environment variable configuration

## API Key Permissions

| Key Type | Permissions | Rate Limit |
|----------|-------------|------------|
| **Primary** | Read forms, Read submissions | 1000/hour |
| **Limited** | Read forms only | 100/hour |
| **Test** | Full access (dev mode only) | Unlimited |

## Security Features

- API key validation
- Rate limiting per key type
- IP address logging
- Request validation
- CORS support
- Development mode restrictions

## Plugin Settings

Navigate to **Settings → Plugins → Formie REST API** for:
- Plugin information
- Available endpoints overview
- Documentation links

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-formie-rest-api](https://github.com/LindemannRock/craft-formie-rest-api)
- **Issues**: [https://github.com/LindemannRock/craft-formie-rest-api/issues](https://github.com/LindemannRock/craft-formie-rest-api/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the [Craft License](https://craftcms.github.io/license/). See [LICENSE.md](LICENSE.md) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)

Built for use with [Formie](https://verbb.io/craft-plugins/formie) by Verbb