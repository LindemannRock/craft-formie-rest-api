# Test page

Try the API from inside the Control Panel — no curl, no Postman, no leaving Craft. Pick a key, choose an endpoint, set some filters, and see the exact status, headers, body, and the equivalent `curl` command. It's the fastest way to confirm a key works and to see a real response shape before you wire up a consumer.

Find it at **Formie REST API → Settings → Test**. It works regardless of `devMode`, though the `/api/test/formie/*` endpoints themselves only resolve when `devMode` is on.

## What you'll use it for

- Verifying a new key returns what you expect before handing it to a consumer
- Seeing the real JSON shape of a form or submission response
- Checking that form scoping, date filters, or a sparse `fields` list behave as intended
- Copying a ready-made `curl` command to share or script

## Run a test

1. Go to **Formie REST API → Settings → Test**.

   ![The in-CP API test page](images/test-page.webp)

2. Choose an **API key**: pick a configured key, or choose **paste** to enter a full key (and its signing secret) for this test only — pasted values are never stored.
3. Choose an **endpoint**:
   - `GET /api/v1/formie/forms`
   - `GET /api/v1/formie/forms/{id}`
   - `GET /api/v1/formie/forms/{handle}`
   - `GET /api/v1/formie/submissions`
   - `GET /api/v1/formie/submissions/{id}`
4. Fill in the fields that appear for that endpoint — an ID or handle, or for submissions: `formHandle`, `dateFrom`, `dateTo`, a sparse `fields` list, and `limit`/`offset`.
5. Click **Run Test**.

The result pane shows the response **status** and **time**, the **equivalent curl** command, the **response headers** (including the `X-RateLimit-*` headers), and the **response body**.

## Download the Postman collection

The Test page also has a **Download Postman collection** button. It gives you the collection plus an environment template; the collection's pre-request script computes the HMAC signature automatically (sorting query params first) when a signing secret is set, and skips it for keys without signing. Switch between keys by pasting a different value into the environment.

## Next steps

- [API keys](api-keys.md) — create the key you'll test with
- [Authentication](../developers/authentication.md) — the signing the Postman collection automates
- [API endpoints](../developers/api-endpoints.md) — the full parameter and response reference
