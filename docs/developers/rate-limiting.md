# Rate Limiting

Each API key has its own request budget per hour. Every response advertises the current budget in headers; exceeding it returns `429`.

## How it works

- The limit is **per key**, counted in a fixed **1-hour window**.
- The default is **100 requests/hour**. Set a different limit per key (CP field or `--rate-limit` on the console command).
- The counter is atomic (mutex-serialized) so bursts of concurrent requests can't slip past the limit.
- It **fails open**: if the cache backend or lock is briefly unavailable, requests are allowed rather than blocked.

## Response headers

Every API response — success or `429` — includes:

| Header | Meaning |
|--------|---------|
| `X-RateLimit-Limit` | The key's limit for the window |
| `X-RateLimit-Remaining` | Requests left in the current window |
| `X-RateLimit-Reset` | Unix epoch seconds when the window resets |

When the budget is exhausted, the next request returns:

```
HTTP/1.1 429 Too Many Requests
```

with body message `API rate limit exceeded. Try again later.` Back off until the `X-RateLimit-Reset` time.

## Disabling the limit (kill switch)

Set the environment variable to turn rate limiting off entirely — for every key. Intended for emergencies or controlled load tests, not normal operation.

```bash
# .env
FORMIE_API_RATE_LIMIT_DISABLED=1
```

When set, `checkRateLimit` returns immediately and no counter is incremented.

## Legacy env-var key limits

The deprecated [environment-variable keys](environment-keys.md) derive their limit from the environment and tier instead of a per-key field (e.g. production primary = 1000/hour, production limited = 100/hour). CP-managed keys always use their own configured limit (default 100).
