# API

All routes are under Mautic's legacy API and require the same authentication as `/api/emails`. Responses are JSON.

Base path: `/api/whatsapptemplates`

Permission base: `whatsapp:templates`

## Endpoints

| Method | Path | Action |
| --- | --- | --- |
| `GET` | `/api/whatsapptemplates` | List |
| `GET` | `/api/whatsapptemplates/{id}` | Read one |
| `POST` | `/api/whatsapptemplates/new` | Create |
| `PUT`, `PATCH` | `/api/whatsapptemplates/{id}/edit` | Update |
| `DELETE` | `/api/whatsapptemplates/{id}/delete` | Delete |
| `POST` | `/api/whatsapptemplates/batch/new` | Batch create |
| `PUT`, `PATCH` | `/api/whatsapptemplates/batch/edit` | Batch update |
| `DELETE` | `/api/whatsapptemplates/batch/delete` | Batch delete |
| `POST` | `/api/whatsapptemplates/{id}/submit` | Create or update the template at Meta |
| `POST` | `/api/whatsapptemplates/{id}/sync` | Pull the current Meta status |
| `POST` | `/api/whatsapptemplates/{id}/send` | Send an approved template |

List, read, create, and update responses serialize the template with the groups `whatsappTemplateDetails` and `publishDetails`. That includes the template fields plus Mautic's published flag, date added, and date modified.

## Create

```http
POST /api/whatsapptemplates/new
Content-Type: application/json

{
  "name": "Orca welcome",
  "language": "en_US",
  "category": "MARKETING",
  "headerText": "Hi {contactfield=firstname}",
  "body": "Your plan is {contactfield=company}.",
  "examples": "Asha\nAcme",
  "footer": "Synetal Solutions",
  "buttonText": "Open",
  "buttonUrl": "https://example.com",
  "isPublished": 1
}
```

`name`, `body`, `language`, and `category` are required. `category` must be `MARKETING` or `UTILITY`. An empty `templateName` is generated from `name`, so this example is stored as `orca_welcome`. The new row starts at `metaStatus` `DRAFT`.

Field names are the same as the form: `templateName`, `headerText`, `buttonText`, `buttonUrl`, `metaStatus`, `metaId`, and `metaError`.

## Submit and sync

```http
POST /api/whatsapptemplates/15/submit
```

```http
POST /api/whatsapptemplates/15/sync
```

Neither route takes a body. Both return:

```json
{
  "whatsappTemplate": {
    "id": 15,
    "name": "Orca welcome",
    "templateName": "orca_welcome",
    "metaId": "1234567890",
    "metaStatus": "PENDING"
  }
}
```

Sync before the first successful submit returns an error because there is no Meta template id yet. A Graph error comes back as a `400` with the message Meta returned.

## Send

```http
POST /api/whatsapptemplates/15/send
Content-Type: application/json

{
  "to": "919800000000",
  "contactId": 42,
  "parameters": ["Asha", "Acme"]
}
```

| Field | Required | Behaviour |
| --- | --- | --- |
| `to` | When the contact has no phone | Digits only. `+` and spaces are removed |
| `contactId` | No | Loads the Mautic contact. If `to` is empty, `mobile` is used, then `phone` |
| `parameters` | No | Text values in token order. When omitted and `contactId` is set, values are read from the contact |

Success:

```json
{
  "success": true,
  "wamid": "wamid.HBgM...",
  "status": "accepted",
  "to": "919800000000"
}
```

The send is rejected with `400` when the template is not `APPROVED`, the phone number is empty, or WhatsApp Settings is incomplete.

## UI routes

These routes require a logged-in Mautic user. They are the screens behind the API.

| Path | Screen |
| --- | --- |
| `/s/whatsapp` | Template list |
| `/s/whatsapp/new` | Create form |
| `/s/whatsapp/edit/{id}` | Edit form |
| `/s/whatsapp/view/{id}` | Detail, submit, and refresh |
| `/s/whatsapp/delete/{id}` | Delete |
