# Local Development Setup and Operational Notes

Date: 2026-03-20
Environment: XAMPP local, Drupal 9.5.11
Base URL: `http://localhost`

## Access and Roles

- Moderator role label: `WG`.
- Moderator role machine name: `wg`.
- Admin account validated with roles:
  - `administrator`
  - `company`
  - `wg`

## Local Login/CAPTCHA Adjustments

For localhost testing:
- Disabled CAPTCHA for login form:
  - `captcha.captcha_point.user_login_form status = false`
- Disabled CAPTCHA for registration form:
  - `captcha.captcha_point.user_register_form status = false`

Reason:
- Production reCAPTCHA domain restrictions block localhost.

## Registration and Visibility Checks

- Registration routes found:
  - `/user/register`
  - `/user/register/{rid}`
- Candidate registration URL:
  - `http://localhost/candidate-registration`
- Admin users page:
  - `http://localhost/admin/people`

## Email/Mail Catcher Setup

### Existing mail system
- Symfony Mailer enabled.
- User registration email policies configured.
- Production SMTP transport (`smtp_ispa`) exists.

### Local MailHog
- Binary:
  - `C:\mailhog\MailHog.exe`
- MailHog transport configured in Drupal:
  - `symfony_mailer.mailer_transport.mailhog`
  - Host `localhost`, Port `1025`
- Default transport switched to `mailhog`.
- MailHog endpoints:
  - SMTP: `localhost:1025`
  - UI: `http://localhost:8025`

## Rendering Changes After Code Updates

No full project restart is normally required.

Use this sequence after changes:
1. Rebuild Drupal cache: `drush cr`
2. Hard refresh browser: `Ctrl+F5`
3. If still stale, use private/incognito window.
4. Re-login if role/permission changes were made.
