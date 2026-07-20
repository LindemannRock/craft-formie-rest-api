# Permissions

These are **Craft user permissions** for Control Panel users (humans) — assigned via **Settings → Users → User Groups → [Group Name] → Formie REST API**. They are separate from **API key scopes**, which gate external HTTP clients; see [Two permission systems](#two-permission-systems) below.

## Permission structure

| Permission | Description |
|------------|-------------|
| **`formieRestApi:manageSettings`** | Access the plugin's Settings pages |
| **`formieRestApi:manageApiKeys`** | Access the API Keys section and view keys within the user's Formie submission ACL |
| └─ `formieRestApi:createApiKeys` | Create new API keys |
| └─ `formieRestApi:editApiKeys` | Edit existing API keys |
| └─ `formieRestApi:revokeApiKeys` | Revoke (delete) API keys |
| **`formieRestApi:viewSystemLogs`** | View the plugin's logs (Logging Library) |
| └─ `formieRestApi:downloadSystemLogs` | Download log files |

## Checking permissions

In Twig:

```twig
{% if currentUser.can('formieRestApi:manageApiKeys') %}
    {# User can see the API Keys section #}
{% endif %}
```

In PHP:

```php
if (Craft::$app->getUser()->checkPermission('formieRestApi:manageApiKeys')) {
    // ...
}

// In a controller
$this->requirePermission('formieRestApi:manageApiKeys');
```

## Nested permission pattern

Craft's nested permissions are a UI convenience — a parent does **not** automatically grant its children.

- **`manageApiKeys`** grants access to the section and the manageable-key list (read).
- **`createApiKeys` / `editApiKeys` / `revokeApiKeys`** each gate their specific write action and must be granted on top of `manageApiKeys`.
- **`viewSystemLogs`** controls the Logs nav; **`downloadSystemLogs`** adds the download action.

To give a user read-only access to keys, grant `manageApiKeys` alone. For full control, also grant the create/edit/revoke children.

## Two permission systems

Formie REST API has **two separate permission systems** — both called "permissions" but with different audiences. Don't confuse them.

| | Craft user permissions (this page) | API key scopes |
|---|---|---|
| **Audience** | Logged-in CP users (humans) | External clients sending `X-API-Key` |
| **Where defined** | This plugin's permission registration, plus Formie's submission ACL when delegating form access | Per key, in the CP |
| **Where assigned** | Settings → Users → User Groups | Formie REST API → API Keys |
| **What's enforced** | Access to settings, API-key management, logs | `read_forms`, `read_submissions`, and per-key form scoping on each REST endpoint |
| **Failure status** | 403 / login redirect in the CP | 401 / 403 from the API |

A CP user with `manageApiKeys` decides what an external consumer's key can do, but cannot delegate or manage broader form access than their own Formie submission permissions. Formie's global `formie-viewSubmissions` permission exposes every key and allows **All forms**. Otherwise, the API-key list, editor, and every write action are limited to keys whose full form scope matches the user's `formie-viewSubmissions:{formUid}` permissions. The consumer's access is then enforced by the capabilities saved on **their** key. See [API keys](../feature-tour/api-keys.md) and [Authentication](authentication.md).
