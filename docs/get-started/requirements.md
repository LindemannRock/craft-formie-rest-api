# Requirements

## System Requirements

| Requirement | Version |
|-------------|---------|
| [Craft CMS](https://craftcms.com/) | 5.0+ |
| [PHP](https://php.net/) | 8.2+ |

## Dependencies

Composer pulls these packages automatically. Craft plugin dependencies also need to be installed in the Control Panel.

| Package | Version | Purpose |
|---------|---------|---------|
| [verbb/formie](https://verbb.io/craft-plugins/formie) | 3.0+ | The forms plugin whose forms and submissions this API exposes — required, install in CP |
| [lindemannrock/craft-plugin-base](https://github.com/LindemannRock/craft-plugin-base) | 5.0+ | Shared base plugin utilities (helpers, traits, layouts) |
| [lindemannrock/craft-logging-library](https://github.com/LindemannRock/craft-logging-library) | 5.0+ | Optional — install in CP for log viewing |

The API exposes Formie data over HTTP, so Formie must be installed and enabled. Every API request is written to an access log through the Logging Library; install it in the Control Panel to view those logs there.
