# Quickstart

Get Formie REST API running in under 5 minutes. By the end you'll have an API key and a working authenticated request returning your Formie forms as JSON.

## 1. Install the plugin

See [Installation](installation.md) for full details.

```bash title="Composer"
composer require lindemannrock/craft-formie-rest-api && php craft plugin/install formie-rest-api
```

```bash title="DDEV"
ddev composer require lindemannrock/craft-formie-rest-api && ddev craft plugin/install formie-rest-api
```

## 2. Create an API key

In the Control Panel — no code:

1. Go to **Formie REST API → API Keys** and click **New API key**.
2. Give it a **Name** (e.g. "Reporting integration").
3. Under **Allowed forms**, turn on **All forms** (or pick specific forms).
4. Leave **Read submissions** on if the consumer needs submission data.
5. **Save.**

The plaintext key (and its signing secret) are shown **once**, right after saving. Copy them now — they're never shown again.

> [!TIP]
> Prefer the command line? `php craft formie-rest-api/api-keys/create --name="Reporting" --forms="*"` prints a key the same way. See [Console commands](../developers/console-commands.md).

## 3. Make your first request

Send the key in the `X-API-Key` header:

```bash
curl -H "X-API-Key: fra_your-key-here" \
     https://yoursite.com/api/v1/formie/forms
```

You'll get a JSON envelope listing your forms:

```json
{
  "success": true,
  "data": [
    { "id": 1, "handle": "contactForm", "title": "Contact Form", "status": "enabled", "submissionCount": 42 }
  ],
  "meta": { "total": 1, "limit": 100, "offset": 0, "timestamp": "2026-06-15T12:00:00+00:00" }
}
```

## 4. Read some submissions

```bash
curl -H "X-API-Key: fra_your-key-here" \
     "https://yoursite.com/api/v1/formie/submissions?formHandle=contactForm&limit=10"
```

## What's next

- [API keys](../feature-tour/api-keys.md) — scope keys, require signing, restrict by IP, set rate limits
- [Authentication](../developers/authentication.md) — add HMAC request signing for production
- [API endpoints](../developers/api-endpoints.md) — every endpoint, parameter, and response shape
- [Test page](../feature-tour/test-page.md) — try endpoints from inside the Control Panel
