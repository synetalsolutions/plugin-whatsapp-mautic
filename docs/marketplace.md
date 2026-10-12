# Listing this plugin in the Mautic community

Mautic's Marketplace only shows packages that are public Composer packages of type `mautic-plugin`, and during the Marketplace beta a Mautic core reviewer also has to allow the package. Publishing the code is not the same step as appearing inside Mautic's Marketplace screen.

Official guides:

- [Listing a plugin in the Marketplace](https://devdocs.mautic.org/en/7.1/marketplace/listing.html)
- [Allow list: what it is and why it is needed](https://devdocs.mautic.org/en/7.1/marketplace/allowlist_what_and_why.html)
- Allowlist repository: <https://github.com/mautic/marketplace-allowlist>
- Application form: <https://mau.tc/marketplace-allowlist>

## 1. Publish the Git repository

This directory is the repository root. The remote should be a public GitHub repository so Packagist and reviewers can read it.

Suggested repository name: `plugin-whatsapp`.

Tag the release that Packagist should build:

```bash
git tag -a 1.0.0 -m "WhatsApp templates for Mautic 7"
git push origin 1.0.0
```

`composer.json` in this directory already has the fields the Marketplace filters on:

| Key | Value |
| --- | --- |
| `name` | `synetalsolutions/plugin-whatsapp` |
| `type` | `mautic-plugin` |
| `license` | `GPL-3.0-or-later` |
| `require.php` | `>=8.2` |
| `require.mautic/core-lib` | `^7.0` |
| `extra.install-directory-name` | `MauticWhatsAppBundle` |

`install-directory-name` must stay identical to the PHP namespace segment. A typo here installs the bundle into the wrong folder and can break plugin loading.

## 2. Publish to Packagist

1. Create a Packagist account and submit the public GitHub repository.
2. Confirm the package name is `synetalsolutions/plugin-whatsapp`.
3. Enable the GitHub webhook so new tags become versions.

Mautic installs the package with Composer into `plugins/MauticWhatsAppBundle`. Users on a zip install can still copy that directory by hand. See [Installation](installation.md).

## 3. Ask Mautic to allow the package

Open <https://mau.tc/marketplace-allowlist> and submit the Packagist name. The core team reviews it and, when they accept it, adds an entry to `allowlist.json`:

```json
{
  "package": "synetalsolutions/plugin-whatsapp",
  "display_name": "WhatsApp templates",
  "minimum_mautic_version": "7.0.0"
}
```

The reviewer emails the address used on the form. Until that entry exists, the package can be installed with Composer and will not be offered in Mautic's Marketplace browser. Mautic caches the allowlist for 60 minutes.

## 4. What reviewers will look at

- The bundle loads on Mautic 7 without a PHP error.
- `mautic:plugins:reload` creates `whatsapp_templates` and `whatsapp_messages`.
- Credentials are configuration fields, not hard-coded tokens.
- The webhook rejects a bad verify token and, when an app secret is set, a bad signature.
- Sending is limited to templates Meta has approved.
- The license is GPL-3.0-or-later.
