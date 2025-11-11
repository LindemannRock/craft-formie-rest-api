# Formie REST & GraphQL API Plugin

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-formie-rest-api.svg)](https://packagist.org/packages/lindemannrock/craft-formie-rest-api)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![Formie](https://img.shields.io/badge/Formie-3.0+-purple.svg)](https://verbb.io/craft-plugins/formie)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-formie-rest-api.svg)](LICENSE)

A comprehensive API plugin for Craft CMS that provides both REST and GraphQL access to Formie forms and submissions data. This plugin enables external systems (like SAP) to retrieve form data through authenticated endpoints using their preferred API style.

## Requirements

- Craft CMS 5.0 or greater
- PHP 8.2 or greater
- Formie 3.0 or greater

## Overview

The Formie REST API plugin provides:
- **REST API**: Custom RESTful endpoints for forms and submissions
- **GraphQL API**: Full access to Formie's native GraphQL schema
- **Dual Authentication**: API key (REST) and token-based (GraphQL) authentication
- **Flexible Querying**: Simple REST calls or powerful GraphQL queries
- **Complete Documentation**: Examples for both API styles
- **Test Endpoints**: Development-friendly test endpoints

## Quick Comparison: REST vs GraphQL

| Feature | REST API | GraphQL API |
|---------|----------|-------------|
| **Best For** | Simple integrations, fixed data needs | Complex queries, flexible data needs |
| **Authentication** | X-API-Key header | Bearer token |
| **Data Format** | Fixed JSON structure | Request exactly what you need |
| **Learning Curve** | Familiar to most developers | Requires GraphQL knowledge |
| **Endpoints** | Multiple endpoints | Single endpoint |
| **Over/Under-fetching** | May get too much/little data | Get exactly what you need |

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

Add API keys to your `.env` file:

```bash
# Primary API key with full access
FORMIE_API_KEY="your_primary_api_key_here"

# Limited access key (read forms only)
FORMIE_API_KEY_LIMITED="your_limited_api_key_here"

# Development test key (dev mode only)
FORMIE_API_KEY_TEST="your_test_api_key_here"
```

## Authentication

### REST API Authentication
Include API key in request headers:
```bash
curl -H "X-API-Key: your-api-key-here" https://yoursite.com/api/v1/formie/forms
```

### GraphQL Authentication
Create a token in **GraphQL → Tokens** and use Bearer authentication:
```bash
curl -H "Authorization: Bearer YOUR_GRAPHQL_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"query": "{ formieForm(handle: \"contact\") { title } }"}' \
     https://yoursite.com/api
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
| GET | `/api/test/graphql/info` | GraphQL API information |
| GET | `/api/test/graphql/examples` | Example GraphQL queries |

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

### GraphQL Examples

See [GRAPHQL_EXAMPLES.md](GRAPHQL_EXAMPLES.md) for comprehensive GraphQL usage examples.

```bash
# Basic form query
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"query": "{ formieForm(handle: \"contact\") { id title fields { handle name } } }"}' \
     https://yoursite.com/api
```

## API Documentation

- **[API_TEST_GUIDE.md](API_TEST_GUIDE.md)** - Complete REST API testing guide
- **[GRAPHQL_EXAMPLES.md](GRAPHQL_EXAMPLES.md)** - GraphQL query examples and patterns
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

This plugin is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)

Built for use with [Formie](https://verbb.io/craft-plugins/formie) by Verbb