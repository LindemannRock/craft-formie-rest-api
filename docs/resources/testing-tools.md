# Testing tools

Use **Formie REST API → Settings → Test** to try authenticated Formie API requests from inside Craft before you wire up a consumer. Paste a key, choose an endpoint, set the relevant filters, and inspect the exact status, headers, body, and equivalent `curl` command.

![The Formie REST API Settings → Test page with endpoint fields and result output](../images/testing-tools-api-test.webp)

## What you'll use it for

- Verifying a newly created API key returns what you expect
- Checking form scoping, submission access, date filters, sparse `fields`, `limit`, and `offset`
- Testing a key that requires HMAC signing without writing a signature script
- Seeing the real JSON response shape for forms and submissions
- Downloading the bundled Postman collection and environment for testing outside Craft

## Run an API test

Open **Formie REST API → Settings → Test**.

1. Paste the full **API Key**. The field expects the plaintext `fra_...` key value. It is used for this test only and is never stored.
2. Paste the **Signing secret** when the key requires signing. Leave it empty if the key does not require signing.
3. Choose an **Endpoint**:
   - `GET /api/v1/formie/forms`
   - `GET /api/v1/formie/forms/{id}`
   - `GET /api/v1/formie/forms/{handle}`
   - `GET /api/v1/formie/submissions`
   - `GET /api/v1/formie/submissions/{id}`
4. Fill in the fields that appear for that endpoint:
   - **ID** for form or submission detail by ID
   - **Form handle** for form detail by handle
   - **formHandle (optional)**, **dateFrom (optional)**, **dateTo (optional)**, and **fields (optional)** for submission-list tests
   - **limit** and **offset** for list endpoints
5. Click **Run Test**.

The result pane shows **Status**, **Time**, **Equivalent curl**, **Response headers**, and **Response body**. When a signing secret is pasted, the controller signs the request server-side with `X-Timestamp` and `X-Signature` for this test.

The page calls the production `/api/v1/formie/*` endpoints, so it works regardless of `devMode`. The separate `/api/test/formie/*` endpoints are still devMode-only and are documented in [API endpoints](../developers/api-endpoints.md).

## Download the Postman collection

The page includes a **Developer Resources** box with **Download Postman collection**. The download is `formie-rest-api-postman.zip` and includes:

- `Formie-REST-API.postman_collection.json`
- `Formie-REST-API.postman_environment.json`
- `README.md`

The collection's pre-request script computes the HMAC signature automatically when a signing secret is set, including sorted query parameters. Switch between keys by changing the environment values.

## Next steps

- [API keys](../feature-tour/api-keys.md) — create or rotate the key you will test.
- [Authentication](../developers/authentication.md) — understand the HMAC signing the CP and Postman tools automate.
- [API endpoints](../developers/api-endpoints.md) — review every endpoint, parameter, and status.
- [Troubleshooting](troubleshooting.md) — diagnose 401, 403, 429, date-filter, and devMode test-endpoint issues.
