# Changelog

## 1.0.0

- Create, edit, publish, and delete WhatsApp templates in the Mautic UI.
- Store templates locally as drafts and submit them to a Meta WhatsApp Business Account.
- Convert Mautic contact tokens such as `{contactfield=firstname}` into positional WhatsApp placeholders.
- Refresh approval status from Meta and from the `message_template_status_update` webhook.
- Send an approved template to a phone number or a Mautic contact through the legacy API.
- Record each accepted send and update delivery status from the `messages` webhook.
- Configuration screen for the Cloud API token, WABA ID, phone number ID, API version, and webhook secrets.
