# Installation

Requires Mautic 7.0 or newer and PHP 8.2 or newer, with the `json` and `mbstring` extensions. The plugin uses Symfony HttpClient, which ships with Mautic.

The bundle directory name must stay `MauticWhatsAppBundle`. Mautic loads the namespace `MauticPlugin\MauticWhatsAppBundle` from `plugins/`.

## Copy the bundle

Copy this repository into the Mautic `plugins` directory so the bundle class is at:

```text
plugins/MauticWhatsAppBundle/MauticWhatsAppBundle.php
```

Give the files to the same system user that runs PHP, then reload plugins from the Mautic root:

```bash
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

Reload creates two tables:

| Table | Purpose |
| --- | --- |
| `whatsapp_templates` | Local template, Meta id, and approval status |
| `whatsapp_messages` | One row per accepted send, including the WhatsApp message id |

Open Mautic as an administrator. The **WhatsApp templates** item is under **Channels**. New permissions are granted to administrators automatically. Other roles need **WhatsApp templates** enabled under **Settings → Roles**.

## Composer

After this package is published to Packagist as `synetalsolutions/plugin-whatsapp`:

```bash
composer require synetalsolutions/plugin-whatsapp
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

`composer.json` sets `"type": "mautic-plugin"` and `"extra.install-directory-name": "MauticWhatsAppBundle"`, which is what Mautic's Marketplace installer expects. Do not also require `mautic/composer-plugin`. Current Composer installs `mautic-plugin` packages into `plugins/` on its own.

If Mautic was installed from a zip rather than Composer, use the copy method above. Marketplace install from inside Mautic also requires a Composer-managed instance.

## Upgrade

Replace the bundle files, keep `config/local.php`, and run:

```bash
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

`mautic:plugins:reload` on an already installed plugin runs migrations from a `Migrations` directory when that directory exists. Version 1.0 has no migration files because the schema is created from the entity metadata on first install.

## Uninstall

Disable or remove the plugin from **Settings → Plugins**, then remove `plugins/MauticWhatsAppBundle`. Dropping `whatsapp_templates` and `whatsapp_messages` is a separate database decision. Saved credentials remain in Mautic configuration until you clear **WhatsApp Settings**.
