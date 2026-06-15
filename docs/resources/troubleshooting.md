# Troubleshooting

Common issues and how to resolve them. The plugin logs every request; if you have the Logging Library installed, check **Formie REST API → Logs** for per-request detail (partial key, endpoint, IP, status).

## 401 — invalid or missing API key

- Confirm the `X-API-Key` header is present and spelled exactly.
- Confirm the key is **enabled** and not **expired** (Formie REST API → API Keys). Disabled and expired keys both return a generic 401.
- If you rotated the key, make sure the consumer has the new value — the old one is gone for good.

## 401 — missing or invalid request signature

The key has **Require signing** on. Each request must include `X-Timestamp` and `X-Signature`.

1. Check the signature base is exactly `METHOD\nPATH_WITH_QUERY\nTIMESTAMP\nBODY` (literal newlines).
2. **Sort query parameters alphabetically before signing.** A CDN/proxy may reorder them, which breaks an unsorted signature once there are two or more out-of-order params.
3. Check client and server clocks — a timestamp more than 5 minutes off is rejected.
4. Confirm you're signing with the **signing secret**, not the API key.

See [Authentication → HMAC request signing](../developers/authentication.md#hmac-request-signing).

## 401 — IP not allowed

The key has an IP whitelist and the request came from an IP outside it.

- Confirm the client's real public IP is in the key's whitelist (single IP or CIDR).
- **Behind a CDN/proxy?** `getUserIP()` may be seeing the proxy's IP, not the client's. Configure Craft's [`trustedHosts`](https://craftcms.com/docs/5.x/reference/config/general.html#trustedhosts) and proxy headers so the real client IP is detected.

## 403 — permission / form not allowed

The key authenticated but isn't allowed to do this.

- **Submissions endpoint returns 403:** the key has **Read submissions** off. Turn it on (or use a key that has it).
- **A specific form returns 403:** the form is outside the key's **Allowed forms** list. Add it, or use **All forms**. (An out-of-scope form returns 403 whether or not it exists.)

## 429 — rate limit exceeded

The key used its hourly budget. Check the `X-RateLimit-Remaining` and `X-RateLimit-Reset` headers and back off until the reset time. Raise the key's **Rate limit** if the budget is genuinely too low. See [Rate limiting](../developers/rate-limiting.md).

## 400 — invalid date filter

`dateFrom` / `dateTo` must be `YYYY-MM-DD`, `YYYY-MM-DD HH:MM:SS`, or ISO 8601. Anything else returns `400`. A date-only `dateTo` is inclusive of the whole day.

## 400 — form handle not found

The submissions endpoint's `formHandle` filter doesn't match any form. Check the handle (the slug, not the title) and that the form exists.

## Submissions are missing from the response

The API **always excludes** incomplete (draft) and spam submissions — that's the fixed contract, with no opt-in to include them. If a submission is missing, confirm it's complete and wasn't flagged as spam by a captcha/Akismet integration.

## Test endpoints (`/api/test/formie/*`) return 404

The `/api/test/formie/*` endpoints only register when Craft `devMode` is on. Use the production `/api/v1/formie/*` endpoints, or enable `devMode` locally. The in-CP [Test page](../feature-tour/test-page.md) works either way (it calls the production endpoints too).

## No forms or submissions come back

- Confirm Formie has forms, and that the key's **Allowed forms** actually includes them.
- For submissions, confirm there are completed, non-spam submissions for the form and within any date filter.
