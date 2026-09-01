# API keys

Give each consumer of the API its own key, scoped to exactly what it should see. An API key is what an external system sends to authenticate — and it carries the rules for what that system can read: which forms, whether submissions are included, whether requests must be signed, which IPs may connect, how often, and for how long.

You manage keys in the Control Panel under **Formie REST API → API Keys**. Plugin tables store only a hash of each key and the signing secret encrypted at rest. The one-time plaintext reveal uses a short-lived, authenticated server-side session handoff described below.

## What you'll use it for

- Issuing a separate, revocable key per partner, app, or integration
- Limiting a partner to one form and nothing else
- Handing out a forms-only key that can't read submission data
- Requiring signed requests and locking a key to known server IPs
- Pausing or expiring a key without deleting it

## Create a key

In the Control Panel — no code:

1. Go to **Formie REST API → API Keys** and click **New API key**.

   ![API Keys list](../images/api-keys-index.webp)

2. Fill in the fields (below), then **Save**.

   ![API key edit screen](../images/api-keys-edit.webp)

3. **Copy the key and signing secret now** — after saving, you're returned to the create page and they're shown for that one authorized request only.

> [!CAUTION]
> The successful create request puts both plaintext values in your authenticated server-side session for the following create-page request. That authorized read removes them immediately. They are never stored in plugin tables or normal plugin logs. If either value is lost, there's no recovery: revoke the key and create a new one (that's also how you rotate).

## Key settings

| Setting | Description |
|---------|-------------|
| **Name** | A label so you can identify the key in the list — typically the consumer it belongs to. Not exposed to callers |
| **Allowed forms** | **All forms** (current and future) or a checklist of **specific forms**. A scoped key can never read forms outside its list — on any endpoint |
| **IP whitelist** | One entry per line — a single IP or a CIDR range, IPv4 or IPv6. Empty allows any IP |
| **Enabled** | Pause/resume the key without deleting it |
| **Require signing** | When on, every request must carry a valid HMAC-SHA256 signature (see [Authentication](../developers/authentication.md)) |
| **Read submissions** | When off, the key is limited to the forms endpoints and can't read submission data |
| **Rate limit** | Requests per hour. Empty uses the default (100/hour) — see [Rate limiting](../developers/rate-limiting.md) |
| **Valid until** | Optional expiry datetime. Empty = never expires. A non-empty value Craft can't parse is rejected and left in the field for correction; it is never silently changed to “never expires” |

Users with **Edit API keys** can open an existing key's edit screen, which also shows read-only details: the key's **status** (Enabled / Disabled / Expired), its **prefix**, **last used** time, and created/updated timestamps.

## Status: enabled, disabled, expired

- **Enabled** — active and within its validity window.
- **Disabled** — turned off by an operator (the **Enabled** switch). Stays configured; rejects requests.
- **Expired** — past its **Valid until** datetime.

Disabled and expired keys both fail authentication with a generic `401` — no detail leaks to the caller about why.

## Revoke vs disable

- **Disable** pauses a key — flip **Enabled** off. Reversible.
- **Revoke** permanently deletes it (the **Revoke** action on the edit screen, or bulk-revoke from the list). Any caller using it immediately gets `401`. There's no undo.

Bulk **enable**, **disable**, and **revoke** are available from the keys list for managing several at once.

## Allowed-forms scoping

A form-scoped key is constrained everywhere: it only lists its allowed forms, only reads those forms' detail, and only returns those forms' submissions. Requesting a form outside the list returns `403` whether or not that form exists — so a probe can't even confirm a form's existence. Turn on **All forms** to grant every form, including ones created later.

The API Keys section also respects the current Control Panel user's Formie submission permissions. Users with Formie's global **View submissions** permission can see and manage every key, select any form, and use **All forms**. Users with per-form submission access only see forms they may delegate and only see keys whose entire form scope they may manage.

That boundary applies to every management action: opening, saving, enabling, disabling, and revoking a key, including bulk actions. A direct URL or forged request for a broader key returns `403` with an explanation. The plugin never silently removes inaccessible forms from an existing key; an operator with access to every form on that key must update it.

Craft's API-key permissions are independent capabilities. **Manage API keys** shows the filtered list only; by itself it shows no New button, editable name, selection checkbox, bulk control, row-action column/menu, or ID column, and direct create/edit routes return `403`. Add **Create API keys** for the create form and create request, **Edit API keys** for existing-key editing and bulk enable/disable, and **Revoke API keys** for single and bulk revoke. There is no read-only detail page.

## Creating keys from the command line

For provisioning or CI, create the same kind of key headlessly:

```bash
php craft formie-rest-api/api-keys/create --name="Partner integration" --forms=contactForm --rate-limit=200
```

The plaintext key and secret print once to stdout. See [Console commands](../developers/console-commands.md) for every option.

## Next steps

- [Authentication](../developers/authentication.md) — send the key, add HMAC signing, restrict by IP
- [API endpoints](../developers/api-endpoints.md) — what each scope unlocks
- [Testing tools](../resources/testing-tools.md) — try a key without leaving the CP
