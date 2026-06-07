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
- If set → computes `HMAC-SHA256(method\npath+query\ntimestamp\nbody, signing_secret)` and stores both as collection variables. `path+query` is the request path including the query string (e.g. `/api/v1/formie/submissions?limit=100&offset=0`).

Each request's Headers tab references `{{api_key}}`, `{{hmac_ts}}`, and `{{hmac_sig}}`. The same collection works for signed and unsigned keys.

## Switching keys

There is one environment, not one per key. To test a different key (Primary, Limited, or Test), paste a different value into `api_key` (and `signing_secret` if that key requires signing). No collection edits needed — the tier is determined by the key string you use, not by a separate environment.

## Response tests

Every request has a `test` script that asserts a `200` status, a JSON body, and `success: true` (plus `meta.total` on list endpoints and `data.authenticated` on Test auth). Run the whole collection with Postman's Collection Runner to smoke-test the API in one pass.

## Notes

- The collection uses `crypto-js` (built into Postman) for HMAC. Tested on Postman v12 (12.8.1).
- If the script logs `Cannot find module 'crypto-js'`, your Postman version is too old — upgrade to a recent build.
- Test endpoints (`/api/test/formie/*`) only exist on the server when `devMode = true`. They return 404 on production builds.
- See the plugin README for endpoint reference and signing-base specification.
