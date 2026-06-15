# Console Commands

Formie REST API provides console commands for creating keys headlessly (CI / provisioning) and a help command.

## `formie-rest-api/help`

Lists the available commands with examples and notes.

```bash title="PHP"
php craft formie-rest-api/help
```

```bash title="DDEV"
ddev craft formie-rest-api/help
```

For one command: `php craft formie-rest-api/help api-keys/create`. Craft's native signature help also works: `php craft help formie-rest-api/api-keys/create`.

## `formie-rest-api/api-keys/create`

Creates a CP-managed (database-backed) API key — the same kind the [API Keys](../feature-tour/api-keys.md) page manages — and prints the plaintext key and signing secret to stdout **exactly once**. Only a hash of the key and the encrypted secret are stored; if you lose the printed values, create a new key.

```bash title="PHP"
php craft formie-rest-api/api-keys/create --name="Partner integration" --forms=contactForm,productRating
```

```bash title="DDEV"
ddev craft formie-rest-api/api-keys/create --name="Partner integration" --forms=contactForm,productRating
```

**Options:**

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `--name` | `string` | (required) | Human-readable label for the key |
| `--forms` | `string` | `''` | Comma-separated form handles, or `*` for all forms. Empty is only valid with `--disabled` (draft key) |
| `--ip-whitelist` | `string` | `''` | Comma-separated IPs / CIDR ranges (IPv4 or IPv6). Empty = all IPs allowed |
| `--rate-limit` | `int` | `100` | Requests per hour |
| `--valid-until` | `string` | (none) | Optional expiry datetime (any format Craft can parse). Empty = never expires |
| `--no-submissions` | flag | off | Limit the key to the forms endpoints (no submission data) |
| `--no-signing` | flag | off | Don't require HMAC signing. The secret is still generated and stored, so signing can be enabled later in the CP |
| `--disabled` | flag | off | Create the key disabled (a draft) |

```bash
# A reporting key for all forms, forms-only, custom rate limit
php craft formie-rest-api/api-keys/create --name="Reporting" --forms="*" --no-submissions --rate-limit=200
```

> [!NOTE]
> An enabled key must allow at least one form (or `*`). An empty `--forms` is accepted only together with `--disabled`, producing a draft key you widen later.

## `formie-rest-api/security/generate-key` (legacy)

> [!WARNING]
> Deprecated. This generates legacy **environment-variable** keys, kept only as a migration bridge and slated for removal. For new keys use `api-keys/create` (or the CP). See [Environment-variable keys](environment-keys.md).

Interactive generator for the `primary`, `limited`, `test`, or `all` env-var key slots. It prints the generated key (and an optional paired signing secret) and can write a consolidated block to your `.env` or print it for manual paste.

```bash title="PHP"
php craft formie-rest-api/security/generate-key
```

```bash title="DDEV"
ddev craft formie-rest-api/security/generate-key
```

Test keys only generate when Craft `devMode` is enabled.
