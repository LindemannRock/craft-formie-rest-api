# Formie REST API — Postman files

Generic Postman collection + environment templates for the [Formie REST API](https://github.com/LindemannRock/craft-formie-rest-api) plugin.

## Files

- **`Formie-REST-API.postman_collection.json`** — the collection. All endpoints (production + test). Collection-level pre-request script signs every request via HMAC when `signing_secret` is set on the active environment.
- **`Formie-REST-API-Primary.postman_environment.json`** — environment template for the Primary key (full read access).
- **`Formie-REST-API-Limited.postman_environment.json`** — environment template for the Limited key (forms only).
- **`Formie-REST-API-Test.postman_environment.json`** — environment template for the Test key (devMode only, full read access).

## Setup

1. Import all four files into Postman.
2. Open the environment you want to use.
3. Set:
   - `base_url` → your site URL (e.g. `https://yoursite.com`, no trailing slash)
   - `api_key` → the matching API key from `.env` (`FORMIE_API_KEY`, `FORMIE_API_KEY_LIMITED`, or `FORMIE_API_KEY_TEST`)
   - `signing_secret` → matching signing secret if signing is enabled for this key (`FORMIE_API_SIGNING_SECRET[_*]`). **Leave empty if signing is not enabled.**
4. Optionally set `form_id`, `form_handle`, `submission_id` for the relevant requests.
5. Pick the environment from Postman's top-right dropdown and run any request.

## How signing works

The collection has a single pre-request script (collection-level, runs before every request):

- Reads `signing_secret` from the active environment.
- If empty → sets `hmac_ts` and `hmac_sig` collection variables to empty strings; the empty headers are ignored by the server for keys that don't require signing.
- If set → computes `HMAC-SHA256(method\npath\ntimestamp\nbody, signing_secret)` and stores both as collection variables.

Each request's Headers tab references `{{api_key}}`, `{{hmac_ts}}`, and `{{hmac_sig}}`. The same collection works for signed and unsigned keys.

## Switching tiers

Just change the active environment in Postman's dropdown. No collection edits needed.

## Notes

- The collection uses `crypto-js` (built into Postman) for HMAC. Tested on Postman v12 (12.8.1).
- If the script logs `Cannot find module 'crypto-js'`, your Postman version is too old — upgrade to a recent build.
- Test endpoints (`/api/test/formie/*`) only exist on the server when `devMode = true`. They return 404 on production builds.
- See the plugin README for endpoint reference and signing-base specification.
