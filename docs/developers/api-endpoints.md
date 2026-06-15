# API Endpoints

The REST API exposes Formie forms and submissions as JSON over HTTP. Every endpoint is `GET`, authenticated with an [API key](../feature-tour/api-keys.md), and returns the same envelope shape.

All endpoints require the `X-API-Key` header (and, when the key requires it, HMAC signing headers). See [Authentication](authentication.md).

## Versioning

The API URL carries its own version (`/api/v1/`) independent of the plugin version. The plugin version bumps on every release; the `/api/v1/` URL only changes on a breaking wire-format change. An integrator targeting `/api/v1/` keeps working across plugin updates as long as the contract is unchanged.

## Response envelope

Every successful response is a JSON object:

```json
{
  "success": true,
  "data": [ /* … */ ],
  "meta": {
    "total": 42,
    "limit": 100,
    "offset": 0,
    "timestamp": "2026-06-15T12:00:00+00:00"
  }
}
```

- **List** endpoints return an array in `data` and include `total` / `limit` / `offset` in `meta`.
- **Detail** endpoints return a single object in `data` and a `meta` with just `timestamp`.
- All datetimes are **ISO 8601 in the site timezone**.

Errors use standard HTTP status codes with a JSON body (Craft's exception format). See [Status codes](#status-codes).

## Production endpoints

| Method | Endpoint | Scope required | Description |
|--------|----------|----------------|-------------|
| GET | `/api/v1/formie/forms` | `read_forms` | List forms |
| GET | `/api/v1/formie/forms/{id}` | `read_forms` | Get a form by numeric ID |
| GET | `/api/v1/formie/forms/{handle}` | `read_forms` | Get a form by handle |
| GET | `/api/v1/formie/submissions` | `read_submissions` | List submissions |
| GET | `/api/v1/formie/submissions/{id}` | `read_submissions` | Get a submission by ID |

A key that is form-scoped only ever sees its allowed forms — on every endpoint. Requesting a form outside the allowlist returns `403` whether or not that form exists (no existence leak).

### List forms

`GET /api/v1/formie/forms`

| Param | Default | Description |
|-------|---------|-------------|
| `status` | `enabled` | Form status. Pass `all` for any status |
| `limit` | `100` | Page size |
| `offset` | `0` | Page offset |

Each form in `data`:

```json
{
  "id": 1,
  "uid": "…",
  "handle": "contactForm",
  "title": "Contact Form",
  "status": "enabled",
  "dateCreated": "2026-01-01T09:00:00+00:00",
  "dateUpdated": "2026-02-01T09:00:00+00:00",
  "submissionCount": 42
}
```

`submissionCount` counts completed, non-spam submissions (matching the submissions endpoint).

### Get a form (detail)

`GET /api/v1/formie/forms/{id}` or `GET /api/v1/formie/forms/{handle}`

Returns the same form object plus full metadata: `appearance`, `behaviour`, `privacy`, `restrictions`, `template`, a `fields` array (field schema), and a `pages` array (per-page settings, conditions, and field list). See [Field reference](field-reference.md) for field metadata shapes.

### List submissions

`GET /api/v1/formie/submissions`

| Param | Default | Description |
|-------|---------|-------------|
| `formHandle` / `formId` | (none) | Restrict to a single form |
| `status` | `live` | Submission status. Pass `all` for any status |
| `limit` | `100` | Page size |
| `offset` | `0` | Page offset |
| `dateFrom` / `dateTo` | (none) | Filter by `dateCreated`. Accepts `YYYY-MM-DD`, `YYYY-MM-DD HH:MM:SS`, or ISO 8601. A date-only `dateTo` is inclusive of the whole day |
| `fields` | (all) | **Sparse fieldset** — comma-separated field handles (`fields=rating,email`). The `fields` map then contains only those handles; unknown handles are ignored |

Results are ordered newest first (`dateCreated DESC`). **Incomplete (draft) and spam submissions are always excluded** — this is the fixed API contract, with no opt-in to include them.

> [!TIP]
> Use `dateFrom` for incremental sync — pull only submissions created since your last run instead of re-fetching everything. Combine with a sparse `fields` list to make large pulls faster.

```bash
curl -H "X-API-Key: fra_…" \
     "https://yoursite.com/api/v1/formie/submissions?formHandle=productRating&dateFrom=2026-06-09T14:00:00&fields=rating,email"
```

Each submission in `data`:

```json
{
  "id": 123,
  "uid": "…",
  "formId": 1,
  "formHandle": "contactForm",
  "status": "live",
  "dateCreated": "2026-06-10T10:30:00+00:00",
  "dateUpdated": "2026-06-10T10:30:00+00:00",
  "fields": {
    "email": { "label": "Email", "handle": "email", "type": "Email", "value": "jane@example.com", "required": true }
  }
}
```

See [Field reference](field-reference.md) for the per-field-type value shapes.

### Get a submission (detail)

`GET /api/v1/formie/submissions/{id}`

Returns the same submission object plus a nested `form` object. Also honours the `fields` sparse-fieldset param.

## Test endpoints (devMode only)

These mirror the production read endpoints and exist for local verification. They are **only registered when Craft `devMode` is on**.

| Method | Endpoint |
|--------|----------|
| GET | `/api/test/formie/forms` |
| GET | `/api/test/formie/submissions` |
| GET | `/api/test/formie/auth` |

The in-CP [Test page](../feature-tour/test-page.md) calls these (and the production endpoints) for you.

## Status codes

| Code | When |
|------|------|
| `200` | Success |
| `400` | Bad request — an unparseable `dateFrom`/`dateTo`, or a `formHandle` filter that doesn't match a form |
| `401` | Missing or invalid API key, missing/invalid HMAC signature, or a client IP outside the key's whitelist |
| `403` | The key lacks the required scope (`read_forms` / `read_submissions`) or the form is outside its allowlist |
| `404` | The requested form or submission doesn't exist |
| `429` | Rate limit exceeded — see [Rate limiting](rate-limiting.md) |

See [Troubleshooting](../resources/troubleshooting.md) for resolving each.
