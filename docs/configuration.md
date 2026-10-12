# Configuration

Open **Settings → Configuration → WhatsApp Settings**.

| Field | Meta value | Used for |
| --- | --- | --- |
| Access token | Permanent or system-user token with `whatsapp_business_messaging` and `whatsapp_business_management` | Every Cloud API call |
| WhatsApp Business Account ID | WABA ID | `POST /{WABA_ID}/message_templates` |
| Phone number ID | Phone number ID, not the display number | `POST /{PHONE_NUMBER_ID}/messages` |
| Graph API version | Default `v23.0` | Prefix of `https://graph.facebook.com/` |
| Webhook verify token | A secret you choose | Meta's GET verification challenge |
| App secret | Meta app secret | Checks `X-Hub-Signature-256` on webhook POST |

Save the form. Mautic writes these values into its local configuration. Leave the token and secret empty in git and in screenshots.

The plugin reads the keys `whatsapp_access_token`, `whatsapp_waba_id`, `whatsapp_phone_number_id`, `whatsapp_api_version`, `whatsapp_webhook_verify_token`, and `whatsapp_app_secret`.

Submit and send both check that the access token, WABA ID, and phone number ID are present. If any of them is empty, the API returns a clear error and the template stays in its previous status.

## Meta app

1. In the Meta developer app, add the WhatsApp product and copy the phone number ID and WABA ID.
2. Create a token that can manage message templates and send messages on that WABA.
3. Set the callback URL to `https://your-mautic-host/whatsapp/webhook`.
4. Use the same verify token you saved in Mautic.
5. Subscribe to the `messages` field and to `message_template_status_update`.

The webhook path is public. It is outside `/s` and `/api`, so anonymous Meta requests can reach it. Requests to `/api/whatsapptemplates` still require Mautic API authentication.

## Permissions

The permission name is `whatsapp:templates`. It follows Mautic's extended set: view own, view others, edit own, edit others, create, delete own, delete others, and publish.

The UI and the legacy API both use that permission. A user who can edit a template can submit it and refresh its status. A user who can view it can send it.
