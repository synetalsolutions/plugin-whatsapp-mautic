# Contributing

This plugin is licensed under GPL-3.0-or-later, the same license as Mautic. Contributions must be compatible with that license.

## Development setup

1. Install Mautic 7.0 or newer with PHP 8.2 or newer.
2. Clone this repository into `plugins/MauticWhatsAppBundle`.
3. Reload the plugin:

```bash
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

Run those commands as the same user that owns the Mautic files.

## Code style

- PHP 8.2, `declare(strict_types=1)`, PSR-12.
- Four-space indentation and short array syntax.
- Keep imports at the top of the file.
- Do not commit access tokens, app secrets, or a filled-in `config/local.php`.

## Pull requests

Describe the behaviour you changed and how you tested it. For a template, submit, status, or send change, include a request example and the expected Meta payload.

A useful manual check:

1. Save a template whose body contains `{contactfield=firstname}`.
2. Confirm the stored template name is lowercase with underscores.
3. Submit it only after WhatsApp Settings has a token, WABA ID, and phone number ID.
4. Call `POST /api/whatsapptemplates/{id}/send` before approval and confirm Mautic refuses the send.
5. Point Meta's webhook at `/whatsapp/webhook` and confirm a status update changes `whatsapp_messages.status`.
