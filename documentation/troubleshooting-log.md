# Troubleshooting Log

Date: 2026-03-20

## 1. Site 500 Error During Login and General Page Loads

Symptoms:
- Unexpected error pages.
- Plugin not found errors.

Root causes:
- `private_content` field item plugin failed to load due to missing required method.
- MySQL packet size too small during cache/config writes.

Fixes:
- Added `isEmpty()` method in:
  - `web/modules/contrib/private_content/src/Plugin/Field/FieldType/PrivateItem.php`
- Increased MySQL packet size:
  - Runtime: `SET GLOBAL max_allowed_packet=268435456`
  - Persistent: `C:\xampp\mysql\bin\my.ini` -> `max_allowed_packet=256M`
- Ran `drush cr` successfully after fix.

## 2. CAPTCHA Blocking Localhost

Symptoms:
- reCAPTCHA domain restriction blocked login/registration.

Fix:
- Disabled CAPTCHA per form config point for local testing.

## 3. Moderator Access Page 500

Symptoms:
- `http://localhost/moderator/manage-access` returned unexpected error.

Root cause:
- Form class called nonexistent method `entityTypeManager()` on `FormBase`.

Fix:
- Refactored form class to inject `entity_type.manager` service.
- Rebuilt cache and verified form builds successfully.

## 4. Tab Not Visible in UI

Symptoms:
- New local task/tab did not render on expected page.

Actions taken:
- Added direct route access for reliability.
- Added explicit admin menu link placement under People hierarchy.
- Rebuilt caches and verified menu registration.

## 5. Helpful Validation Artifacts

Temporary utilities created in `C:\mailhog` for runtime checks:
- `test_mail.php`
- `test_moderator_access.php`
- `test_build_moderator_form.php`
- `list_moderator_local_tasks.php`
- `insert_transport.sql`
