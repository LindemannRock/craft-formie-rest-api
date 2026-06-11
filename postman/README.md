# Formie REST API — Postman files

Generic Postman collection + environment templates for the [Formie REST API](https://github.com/LindemannRock/craft-formie-rest-api) plugin.

## Files

- **`Formie-REST-API.postman_collection.json`** — the collection. All endpoints (production + test), each with response tests. Collection-level pre-request script signs every request via HMAC when `signing_secret` is set on the active environment.
- **`Formie-REST-API.postman_environment.json`** — the environment template (`base_url`, `api_key`, `signing_secret`, and sample `form_id` / `form_handle` / `submission_id`).

## Setup

1. Import both files into Postman.
2. Open the `Formie REST API` environment.
3. Set:
   - `base_url` → your site URL (e.g. `https://yoursite.com`, no trailing slash)
   - `api_key` → an API key from `.env` (`FORMIE_API_KEY`, `FORMIE_API_KEY_LIMITED`, or `FORMIE_API_KEY_TEST` — paste whichever one you want to test)
   - `signing_secret` → matching signing secret if signing is enabled for that key (`FORMIE_API_SIGNING_SECRET[_*]`). **Leave empty if signing is not enabled.**
4. Optionally set `form_id`, `form_handle`, `submission_id` for the relevant requests.
5. Pick the environment from Postman's top-right dropdown and run any request.

## How signing works

The collection has a single pre-request script (collection-level, runs before every request):

- Reads `signing_secret` from the active environment.
- If empty → sets `hmac_ts` and `hmac_sig` collection variables to empty strings; the empty headers are ignored by the server for keys that don't require signing.
- If set → computes `HMAC-SHA256(method\npath+query\ntimestamp\nbody, signing_secret)` and stores both as collection variables. `path+query` is the request path including the query string (e.g. `/api/v1/formie/submissions?formHandle=productRating&limit=100&offset=0`).

Each request's Headers tab references `{{api_key}}`, `{{hmac_ts}}`, and `{{hmac_sig}}`. The same collection works for signed and unsigned keys.

> **Query params are sorted before signing.** The script sorts query parameters alphabetically before computing the signature. This is required when the site sits behind a CDN/proxy (e.g. Cloudflare) that normalizes query-string order before the request reaches the server — the signature must be computed over the sorted order to match what the server actually receives. If you build a client by hand, **sort your query parameters alphabetically before signing** (the order you send them in doesn't matter; only the order you sign does). A request that signs the unsorted order will fail with `401 Missing or invalid request signature` the moment it has two or more params that aren't already in alphabetical order.

### Where to find the script in Postman

The signing logic is **not** on any individual request — it lives on the collection itself, which is why you won't see it when browsing the requests:

1. In the left sidebar, click the **`Formie REST API`** collection (the top-level item, not a request or folder inside it).
2. Open the **Scripts** tab.
3. Select **Pre-request**.

That's the script that builds `hmac_ts` and `hmac_sig` before every request runs. The per-request **Scripts → Post-response** (older Postman: the **Tests** tab) is a different script — it only asserts the response; it does no signing.

## Switching keys

There is one environment, not one per key. To test a different key, paste a different value into `api_key` (and `signing_secret` if that key requires signing). No collection edits needed.

CP-managed keys (created under **Formie REST API → API Keys**) work identically to env-var keys: paste the key into `api_key` and its signing secret into `signing_secret` — same header, same signing base. Remember both values are only shown once, at creation.

## Response tests

Every request has a `test` script that asserts a `200` status, a JSON body, and `success: true` (plus `meta.total` on list endpoints and `data.authenticated` on Test auth). Run the whole collection with Postman's Collection Runner to smoke-test the API in one pass.

## Notes

- The collection uses `crypto-js` (built into Postman) for HMAC. Tested on Postman v12 (12.8.1).
- If the script logs `Cannot find module 'crypto-js'`, your Postman version is too old — upgrade to a recent build.
- Test endpoints (`/api/test/formie/*`) only exist on the server when `devMode = true`. They return 404 on production builds.
- **List submissions** has a disabled `fields` query param — enable it and set a comma-separated list of field handles (e.g. `rating,email`) to return a sparse fieldset (only those fields per submission). It also has `dateFrom`/`dateTo` for incremental pulls.
- See the plugin README for endpoint reference and signing-base specification.
