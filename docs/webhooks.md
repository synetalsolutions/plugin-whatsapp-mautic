# Webhooks

Public callback:

```text
https://your-mautic-host/whatsapp/webhook
```

Register that URL on the Meta app. Subscribe to `messages` and `message_template_status_update`.

## Verification

Meta sends a GET request when you save the callback. Match the verify token saved in **WhatsApp Settings**.

```http
GET /whatsapp/webhook?hub.mode=subscribe&hub.verify_token=YOUR_TOKEN&hub.challenge=CHALLENGE
```

PHP exposes those query keys as `hub_mode`, `hub_verify_token`, and `hub_challenge`. The plugin accepts either spelling. A matching token responds with the raw challenge string and `200`. Any other GET responds with `403` and the body `Forbidden`. An empty verify token in Mautic never passes.

## Signature

When **App secret** is saved, every POST must include:

```http
X-Hub-Signature-256: sha256=<hex hmac of the raw body>
```

The signature is HMAC-SHA256 of the raw request body using the app secret. A mismatch responds with `403` and `Invalid signature`. When the app secret is empty, the signature check is skipped so you can connect the webhook before adding it.

## Message status

A `messages` change updates `whatsapp_messages` by `wamid`.

```json
{
  "entry": [
    {
      "changes": [
        {
          "field": "messages",
          "value": {
            "statuses": [
              {
                "id": "wamid.HBgM...",
                "status": "delivered",
                "errors": [
                  {
                    "title": "Message undeliverable"
                  }
                ]
              }
            ]
          }
        }
      ]
    }
  ]
}
```

`status` is stored as Meta sent it (`sent`, `delivered`, `read`, `failed`, and the other Cloud API values). The first error title or message is stored in `error`. Statuses for a `wamid` this Mautic has never sent are ignored.

A `200` with body `OK` means Mautic accepted the webhook. It does not mean a matching message row was found.

## Template review

```json
{
  "entry": [
    {
      "changes": [
        {
          "field": "message_template_status_update",
          "value": {
            "message_template_id": "1234567890",
            "event": "APPROVED",
            "reason": "NONE"
          }
        }
      ]
    }
  ]
}
```

The plugin finds the local template by `meta_id` and stores the uppercased `event` as `metaStatus`. `reason` is stored in `metaError` when Meta includes it. Unknown template ids are ignored.
