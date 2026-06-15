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

## Enable log viewing (optional)

Every API request is written to an access log through the [Logging Library](https://github.com/LindemannRock/craft-logging-library). It's pulled in as a Composer dependency — install it to view those logs under **Formie REST API → Logs**:

```bash title="PHP"
php craft plugin/install logging-library
```

```bash title="DDEV"
ddev craft plugin/install logging-library
```

Or via the Control Panel: **Settings → Plugins → Logging Library → Install**.

## Copy config file (optional)

For per-environment settings (plugin name, log level, date/time display), copy the sample config to your project:

```bash
cp vendor/lindemannrock/craft-formie-rest-api/src/config.php config/formie-rest-api.php
```

See [Configuration](configuration.md) for the available options.

## Quick Start

See [Quickstart](quickstart.md) for the fastest path from install to your first authenticated API request.
