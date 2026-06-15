# Features overview

Pull your Formie forms and submissions into another system over HTTP — a SAP/ERP integration, a BI tool, a partner feed, a reporting job — through authenticated, rate-limited REST endpoints you manage from the Control Panel.

> [!TIP]
> New to Formie REST API? Start with [Installation](../get-started/installation.md) and the [Quickstart](../get-started/quickstart.md), then come back here for a tour.

## What it does

Formie REST API adds read-only REST endpoints for Formie's forms and submissions, with its own access control layer: per-consumer API keys, optional HMAC request signing, IP whitelisting, per-key rate limits, and an access log for every request. You create and scope keys in the Control Panel; external systems call the endpoints with an `X-API-Key` header.

It's a server-to-server data API — there's no form rendering or submission-writing here. Each consumer gets its own key, scoped to exactly the forms it should see.

> [!NOTE]
> This is a REST API, separate from Formie's built-in GraphQL. If you want GraphQL, use Formie's own support at Craft's `/api` endpoint. This plugin adds REST with its own auth, rate limiting, and logging.

## What you'll use it for

- Feeding form submissions into a CRM, ERP, or data warehouse (incremental sync via `dateFrom`)
- Giving a partner read access to one specific form, and nothing else
- Powering a reporting/BI dashboard from real submission data
- Exposing form structure (fields, pages, conditions) to a headless or external renderer
- Keeping an auditable log of who pulled what, and when

## Core capabilities

- **[API keys](api-keys.md)** — Create one key per consumer in the CP. Each carries its own allowed-forms list, submissions toggle, signing requirement, IP whitelist, rate limit, and expiry. The plaintext key and signing secret are shown once at creation; only a hash is stored.

- **[REST endpoints](../developers/api-endpoints.md)** — List and read forms and submissions as JSON, with pagination, status and date filters, and sparse fieldsets for lean payloads.

- **[Authentication](../developers/authentication.md)** — `X-API-Key` on every request, plus optional HMAC-SHA256 signing (replay + tamper protection) and IP whitelisting per key.

- **[Rate limiting](../developers/rate-limiting.md)** — A per-key hourly budget with `X-RateLimit-*` headers and `429` on exceed.

- **[Test page](test-page.md)** — Try any endpoint from inside the Control Panel — pick or paste a key, set filters, and inspect the status, headers, body, and equivalent `curl`. Plus a downloadable Postman collection.

- **Access logging** — Every request is logged (partial key fingerprint, endpoint, IP, user agent, status) through the Logging Library, viewable under **Formie REST API → Logs**.

## The Control Panel

Formie REST API has its own nav section:

- **API Keys** — create, scope, enable/disable, and revoke per-consumer keys.
- **Settings → General** — plugin name and log level.
- **Settings → Interface** — date/time display preferences (CP only).
- **Settings → Test** — the live API tester and Postman download.
- **Logs** — the access/request log viewer (Logging Library).

![The Formie REST API control panel section](images/overview-cp-section.webp)

## Next steps

1. [Install the plugin](../get-started/installation.md)
2. [Create your first API key](api-keys.md)
3. [Make your first request](../get-started/quickstart.md)
