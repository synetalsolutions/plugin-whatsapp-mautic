# Templates

A template in this plugin is the WhatsApp equivalent of a Mautic email. You write it once, Meta reviews it, and later sends reuse the approved name.

## Fields

| Field | Limit | Required | Notes |
| --- | --- | --- | --- |
| Name | 191 | Yes | Label inside Mautic |
| WhatsApp template name | 512 | No | Lowercase letters, numbers, and underscores. Generated from the name when empty |
| Language | 16 | Yes | WhatsApp locale, default `en_US` |
| Category | | Yes | `MARKETING` or `UTILITY` |
| Header | 60 | No | Text header. Tokens are allowed |
| Body | | Yes | Tokens are allowed |
| Sample values | | When the text has tokens | One sample per line, in token order |
| Footer | 60 | No | Plain text. Tokens are left as written |
| Button label | 25 | No | Requires a button URL |
| Button URL | 2000 | No | Static `https` URL |
| Published | | | Mautic published flag |

`AUTHENTICATION` templates are outside this version. A URL button is optional and its URL is sent to Meta as a fixed link.

## Tokens

Write tokens the same way as in an email:

```text
Hi {contactfield=firstname}
Your company is {contactfield=company|default=there}.
```

On submit, the plugin walks the header and then the body from left to right. Each `{...}` token becomes the next positional placeholder. The example above is sent to Meta as:

```text
HEADER: Hi {{1}}
BODY:   Your company is {{2}}.
```

Sample lines are matched to that same order. With samples

```text
Asha
Acme
```

Meta receives `Asha` for the header and `Acme` for the body. A missing sample is replaced with the word `sample` so the create call still includes the example block Meta requires.

`|default=there` is kept for sending. When `parameters` is omitted and a contact id is supplied, an empty contact field uses that default.

## Status

| Status | Meaning |
| --- | --- |
| `DRAFT` | Saved in Mautic, not yet accepted by Meta |
| `PENDING` | Submitted, waiting for review |
| `APPROVED` | Meta approved it. Sending is allowed |
| `REJECTED` | Meta rejected it, or a later status event said so |

The first submit calls `POST /{WABA_ID}/message_templates`. A later submit, when `metaId` is already stored, updates that template with `POST /{TEMPLATE_ID}` and the current components.

A failed submit stores the Graph error in `metaError` and leaves the previous status in place.

## Sending

Send is allowed only when `metaStatus` is `APPROVED`. The recipient `to` is reduced to digits. The Cloud API call is:

```http
POST https://graph.facebook.com/v23.0/{PHONE_NUMBER_ID}/messages
```

The body is a `type: template` message. Header and body parameters are text parameters in the same order as the tokens. The returned WhatsApp message id (`wamid`) is stored on `whatsapp_messages` with status `accepted`.

From the template detail page you can **Submit to WhatsApp** and **Refresh status**. Refresh reads `GET /{TEMPLATE_ID}?fields=status,rejected_reason,name`.
