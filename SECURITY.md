# Security

Report a vulnerability privately to the maintainers before opening a public issue.

Do not include a live access token, app secret, or webhook verify token in an issue, pull request, or log paste. Rotate any credential that has been posted.

This plugin stores the Cloud API access token and app secret in Mautic configuration (`config/local.php` after you save the settings). Protect that file the same way you protect the Mautic database password.
