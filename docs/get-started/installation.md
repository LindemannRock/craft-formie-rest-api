# Installation & Setup

> [!NOTE]
> Formie REST API is in active development and not yet available on the Craft Plugin Store. Install via Composer for now.

> [!IMPORTANT]
> Formie REST API needs [Formie](https://verbb.io/craft-plugins/formie) installed and enabled — it exposes Formie's forms and submissions. Composer pulls Formie in automatically; install it under **Settings → Plugins** if it isn't already.

## Composer

Add the package to your project using Composer and the command line.

1. Open your terminal and go to your Craft project:

```bash
cd /path/to/project
```

2. Then tell Composer to require the plugin, and Craft to install it:

```bash title="Composer"
composer require lindemannrock/craft-formie-rest-api && php craft plugin/install formie-rest-api
```

```bash title="DDEV"
ddev composer require lindemannrock/craft-formie-rest-api && ddev craft plugin/install formie-rest-api
```

After installing, a **Formie REST API** section appears in the Control Panel nav.

3. **Optional** — Enable [Logging Library](https://github.com/LindemannRock/craft-logging-library) for log viewing:

> [!NOTE]
> Logging Library is included as a Composer dependency and downloaded automatically. Activate it in Craft to view every API request under **Formie REST API → Logs**.

```bash title="PHP"
php craft plugin/install logging-library
```

```bash title="DDEV"
ddev craft plugin/install logging-library
```

Or via the Control Panel: **Settings → Plugins → Logging Library → Install**.

## Post-Install Setup

Formie REST API works as soon as it's installed — there's no salt to generate or templates to copy.

### Review configuration

The plugin's global settings (display name, log level, date/time display) are optional; sensible defaults apply out of the box. See [Configuration](configuration.md) for the settings reference, config-file overrides, and environment-specific options.

## Quick Start

See [Quickstart](quickstart.md) for the fastest path from install to your first authenticated API request.
