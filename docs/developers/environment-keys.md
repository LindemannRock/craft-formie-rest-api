# Environment-Variable Keys (legacy)

> [!WARNING]
> Deprecated. Environment-variable keys remain supported only as a migration bridge for existing consumers and will be removed in a future release. For new keys, use [API keys](../feature-tour/api-keys.md) (CP-managed) or the [console command](console-commands.md).

Before keys were managed in the Control Panel, the plugin read them from environment variables. Those keys still work, with fixed capability tiers.

## Key slots

| Env var | Scopes | Notes |
|---------|--------|-------|
| `FORMIE_API_KEY` | `read_forms`, `read_submissions` | Primary key |
| `FORMIE_API_KEY_LIMITED` | `read_forms` | Forms only |
| `FORMIE_API_KEY_TEST` | `read_forms`, `read_submissions` | Only active when Craft `devMode` is on |

Use **different values per environment** (local, staging, production) and never share keys across them.

Unlike CP-managed keys, env keys are **not form-scoped** — they can read every form.

## Rate limits

Env-key limits derive from the environment and tier (rather than a per-key setting):

| Environment | Primary | Limited |
|-------------|---------|---------|
| Production | 1000/hour | 100/hour |
| Staging | 500/hour | 50/hour |
| Development | 1000/hour | 1000/hour |

The test key is fixed at 1000/hour. See [Rate limiting](rate-limiting.md).

## Optional HMAC signing

Setting a matching signing-secret env var **auto-enables** required signing for that key:

```bash
FORMIE_API_SIGNING_SECRET="..."           # pairs with FORMIE_API_KEY
FORMIE_API_SIGNING_SECRET_LIMITED="..."   # pairs with FORMIE_API_KEY_LIMITED
FORMIE_API_SIGNING_SECRET_TEST="..."      # pairs with FORMIE_API_KEY_TEST
```

The signature contract is identical to CP keys — see [Authentication → HMAC request signing](authentication.md#hmac-request-signing).

## Optional IP whitelist

Setting a matching IP-whitelist env var (comma-separated IPs / CIDR ranges) **auto-enables** the restriction for that key:

```bash
FORMIE_API_IP_WHITELIST="203.0.113.5,192.168.1.0/24,2001:db8::/32"
FORMIE_API_IP_WHITELIST_LIMITED="..."
FORMIE_API_IP_WHITELIST_TEST="..."
```

Empty/unset = no restriction.

## Generating env keys

The interactive console command generates keys and optional signing secrets and can write them to `.env`:

```bash title="PHP"
php craft formie-rest-api/security/generate-key
```

```bash title="DDEV"
ddev craft formie-rest-api/security/generate-key
```

It prompts for which slot (`primary` / `limited` / `test` / `all`), a key prefix (default `fra_`), and whether to also generate a paired signing secret. See [Console commands](console-commands.md).

## Migrating to CP-managed keys

CP-managed keys add per-key form scoping, a submissions toggle, expiry, and CP visibility — and store the key hashed rather than in plaintext env. To migrate, create an equivalent CP key ([API keys](../feature-tour/api-keys.md)), hand the new key to the consumer, then remove the env var. Both kinds are validated the same way at request time, so they can run side by side during the cutover.
