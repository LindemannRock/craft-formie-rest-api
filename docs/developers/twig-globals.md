# Twig Globals

Formie REST API provides the following global variables in your Twig templates.

## `formieRestApiHelper`

*Provided by `lindemannrock/base`*

| Property | Description |
|----------|-------------|
| `formieRestApiHelper.displayName` | Display name (singular, without "Manager") |
| `formieRestApiHelper.pluralDisplayName` | Plural display name (without "Manager") |
| `formieRestApiHelper.fullName` | Full plugin name (as configured) |
| `formieRestApiHelper.lowerDisplayName` | Lowercase display name (singular) |
| `formieRestApiHelper.pluralLowerDisplayName` | Lowercase plural display name |

### Examples

```twig
{{ formieRestApiHelper.displayName }}
{{ formieRestApiHelper.pluralDisplayName }}
{{ formieRestApiHelper.fullName }}
{{ formieRestApiHelper.lowerDisplayName }}
{{ formieRestApiHelper.pluralLowerDisplayName }}
```

---

