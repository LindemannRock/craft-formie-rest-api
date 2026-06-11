# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in Formie REST API, please report it privately. **Do not open a public GitHub issue** — public disclosure before a fix is available puts other users at risk.

### Preferred: GitHub private advisory

[Open a private security advisory](https://github.com/LindemannRock/craft-formie-rest-api/security/advisories/new). This routes the report directly to maintainers with no public visibility.

### Email fallback

If you can't use GitHub's private reporting, email **security@lindemannrock.com** with:

- A description of the vulnerability
- Steps to reproduce
- Affected version(s)
- Potential impact

We aim to acknowledge reports within **48 hours** and provide a status update within **5 business days**.

## Credential Storage Model

How the plugin stores API credentials at rest:

- **API keys** are stored as one-way **HMAC-SHA256 hashes keyed by Craft's `securityKey`**. The plaintext is shown exactly once at creation and never persisted or logged — a database dump alone does not yield usable keys, and hashes are not portable to another install.
- **HMAC signing secrets** are stored **encrypted** (`Security::encryptByKey()`, also keyed by `securityKey`). Unlike the key, the secret must be recoverable, because the server recomputes request signatures with it. Corrupt or tampered ciphertext decrypts to nothing and the key's signature check then fails closed.
- Only the first 12 characters of a key (`fra_` + 8 hex) are stored in the clear, for display and lookup.
- Consequence of the above: rotating Craft's `securityKey` invalidates all stored key hashes and signing secrets — every API key must then be re-issued.

## Supported Versions

Security fixes are issued for the current major release. Please keep the plugin up to date.

| Version | Supported |
| ------- | --------- |
| 3.x     | ✅        |
| < 3.0   | ❌        |

## Scope

**In scope:**

- Authentication and authorization bypasses
- SQL injection, XSS, CSRF, path traversal, RCE
- Sensitive data exposure or privilege escalation
- Cryptographic weaknesses in plugin code
- API credential exposure or auth bypass on form-data endpoints
- Request smuggling or unintended data exposure via API responses

**Out of scope:**

- Vulnerabilities in Craft CMS core — report to [Craft CMS](https://craftcms.com/security)
- Vulnerabilities in third-party dependencies — report upstream
- Issues requiring physical access, stolen credentials, or social engineering
- Theoretical vulnerabilities without a demonstrable impact
- Findings from automated scanners without manual verification

## Disclosure

After a fix is released, we publish a security advisory crediting the reporter (unless they prefer to remain anonymous). We follow a coordinated disclosure model — please give us reasonable time to patch before publishing details publicly.
