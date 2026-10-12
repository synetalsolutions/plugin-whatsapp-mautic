# WhatsApp templates for Mautic

Create WhatsApp message templates the same way you create emails, then submit them to Meta, track approval, and send them through Mautic's API.

The plugin talks to the [WhatsApp Cloud API](https://developers.facebook.com/docs/whatsapp/cloud-api). Marketing and utility messages outside the 24-hour customer service window must use a template that Meta has approved. This plugin keeps that template in Mautic and only sends it after the status is `APPROVED`.

Tested on Mautic 7.2 with PHP 8.3. Requires Mautic 7.0 or newer and PHP 8.2 or newer.

## What you can do

- Write a template in **Channels → WhatsApp templates**, with a header, body, footer, and one URL button.
- Use Mautic contact tokens such as `{contactfield=firstname}`. On submit they become WhatsApp placeholders `{{1}}`, `{{2}}`, with the sample values Meta requires for review.
- Submit the template to your WhatsApp Business Account and refresh its status.
- Receive template approval and message delivery updates on a public webhook.
- Create, update, submit, sync, and send templates through `/api/whatsapptemplates`.

## Documentation

| Guide | Contents |
| --- | --- |
| [Installation](docs/installation.md) | Copy install, Composer install, plugin reload |
| [Configuration](docs/configuration.md) | Cloud API credentials and Meta app setup |
| [Templates](docs/templates.md) | Fields, tokens, categories, and the Meta payload |
| [API](docs/api.md) | Create, edit, submit, sync, and send |
| [Webhooks](docs/webhooks.md) | Verify the callback and status updates |
| [Marketplace](docs/marketplace.md) | How to list this package for the Mautic community |
| [Contributing](CONTRIBUTING.md) | Local development and pull requests |
| [Changelog](CHANGELOG.md) | Released changes |
| [Security](SECURITY.md) | How to report a vulnerability |

## Quick start

1. Install the plugin and run `php bin/console mautic:plugins:reload`.
2. Open **Settings → Configuration → WhatsApp Settings** and save the access token, WhatsApp Business Account ID, and phone number ID.
3. Create a template. Put one sample value per line in **Sample values**, in the same order as the tokens.
4. Open the template and choose **Submit to WhatsApp**.
5. After Meta approves it, send it:

```http
POST /api/whatsapptemplates/1/send
Authorization: Basic ...
Content-Type: application/json

{
  "to": "919800000000",
  "parameters": ["Asha", "Acme"]
}
```

`200` from Meta means the message was accepted. Delivery, read, and failure arrive later on the webhook and are stored in `whatsapp_messages`.

## Current scope

Version 1.0 covers template authoring, Meta submission, status sync, a single send, and the legacy Mautic API. Authentication templates, media headers, dynamic button URLs, and a campaign "send WhatsApp" action are planned follow-ups.

## License

[GPL-3.0-or-later](LICENSE), the same license as Mautic. Copyright 2026 Synetal Solutions.
