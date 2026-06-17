# Authentication

Every request is authenticated with an API key sent in a header. Two optional layers harden a key further: HMAC request signing and an IP whitelist. All three are configured per key — see [API keys](../feature-tour/api-keys.md).

## API key header

Send the key in the `X-API-Key` header:

```bash
curl -H "X-API-Key: fra_your-key-here" \
     https://yoursite.com/api/v1/formie/forms
```

A missing or unrecognized key returns `401`. The key also determines what you can read — its scopes (`read_forms`, `read_submissions`) and its allowed-forms list are enforced on every endpoint.

## HMAC request signing

For production keys, enable **Require signing** so a leaked key alone isn't enough — the caller must also hold the key's separate **signing secret**. Signing adds:

- **Replay protection** — requests expire after a 5-minute timestamp window.
- **Tamper detection** — the signature covers the method, path, timestamp, and body.

When a key requires signing, every request must include these headers:

| Header | Value |
|--------|-------|
| `X-API-Key` | The API key |
| `X-Timestamp` | Current Unix epoch seconds |
| `X-Signature` | Hex HMAC-SHA256 of the signature base, keyed by the signing secret |

### Signature base

Join these four parts with literal newline (`\n`):

```
METHOD\nPATH_WITH_QUERY\nTIMESTAMP\nBODY
```

For a `GET`, the body is empty. `PATH_WITH_QUERY` includes the query string when present.

> [!IMPORTANT]
> When the path has a query string, **sort the query parameters alphabetically before signing**. A CDN or proxy (e.g. Cloudflare) may reorder query params before the request reaches the server, so an unsorted signature fails with `401 Missing or invalid request signature` once a request has two or more out-of-order params. The order you *send* doesn't matter — only the order you *sign*. (Single-param and already-sorted requests work either way; the server verifies against both the as-received and the sorted form.)

A request whose timestamp is more than 5 minutes from server time is rejected, as is a missing header, a missing signing secret, or a mismatched signature.

### Client examples

```bash
KEY="fra_..."
SECRET="..."
TS=$(date +%s)
PATH_Q="/api/v1/formie/forms"
SIG=$(printf "GET\n%s\n%s\n" "$PATH_Q" "$TS" | openssl dgst -sha256 -hmac "$SECRET" -hex | awk '{print $NF}')

curl -H "X-API-Key: $KEY" \
     -H "X-Timestamp: $TS" \
     -H "X-Signature: $SIG" \
     "https://yoursite.com$PATH_Q"
```

```php
$key = '...';
$secret = '...';
$path = '/api/v1/formie/forms';
$ts = (string) time();
$sig = hash_hmac('sha256', "GET\n{$path}\n{$ts}\n", $secret);
// Send X-API-Key, X-Timestamp, X-Signature headers
```

```js
const crypto = require('crypto');

const method = 'GET';
const pathQ = '/api/v1/formie/forms';   // include the sorted query string when present
const body = '';                        // empty for GET, but still part of the base
const ts = Math.floor(Date.now() / 1000).toString();

const base = `${method}\n${pathQ}\n${ts}\n${body}`;
const sig = crypto.createHmac('sha256', secret).update(base).digest('hex');

const res = await fetch('https://yoursite.com' + pathQ, {
    method,
    headers: {
        'X-API-Key': key,
        'X-Timestamp': ts,
        'X-Signature': sig,
        'Accept': 'application/json',
    },
});
```

> [!TIP]
> The bundled Postman collection signs requests for you (and sorts query params). See the [Test page](../feature-tour/test-page.md) to download it.

## IP whitelist

Restrict which client IPs may use a key — useful for server-to-server integrations on stable infrastructure. Set one or more single IPs or CIDR ranges (IPv4 or IPv6) on the key. An empty whitelist means any IP is allowed; a request from a non-matching IP returns `401`.

```
203.0.113.5
192.168.1.0/24
2001:db8::/32
```

> [!WARNING]
> `getUserIP()` returns whatever address sent the request to PHP. Behind a CDN or reverse proxy, configure Craft's [`trustedHosts`](https://craftcms.com/docs/5.x/reference/config/general.html#trustedhosts) and proxy headers correctly — otherwise the whitelist matches the proxy's IP, not the real client's.

## CORS

The API does **not** send `Access-Control-Allow-*` headers or handle `OPTIONS` preflight — it's designed for server-to-server consumers. A browser client on a different origin can't call it directly; proxy the request through your own backend instead.
