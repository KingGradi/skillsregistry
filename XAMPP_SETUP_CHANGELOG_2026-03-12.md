# XAMPP Setup And Change Log (2026-03-12)

## Scope
This document records the local setup work completed to run this Drupal 9 project on Windows XAMPP, including file changes, infrastructure changes, troubleshooting, and verification.

## Outcome
- Local site is running successfully.
- Verified URL: http://localhost/skillsregistry/web/
- Verified response: HTTP 200
- Verified page title: About | Skills Registry

## Environment Findings
- CLI PHP at start: 8.5.1 (incompatible for Drupal 9 workflows).
- Apache PHP (XAMPP): 8.1.25 (compatible for Drupal 9).
- MySQL: MariaDB 10.4.32 reachable on localhost:3306.

## Project File Changes In Workspace
1. Enabled local override loading in [web/sites/default/settings.php](web/sites/default/settings.php).
- Un-commented local include block so [web/sites/default/settings.local.php](web/sites/default/settings.local.php) is loaded automatically.

2. Added local-only override file [web/sites/default/settings.local.php](web/sites/default/settings.local.php).
- Forced DB cache backend locally: cache.backend.database.
- Unset memcache settings for local dev.
- Added trusted host patterns for localhost, 127.0.0.1, and skillsregistry.local.
- Set local private file path to ../private relative to Drupal web root.
- Disabled CSS/JS preprocessing for dev convenience.
- Enabled rebuild access and permission hardening skip for local troubleshooting.

3. Created troubleshooting artifact [core_extension.yml](core_extension.yml).
- Extracted serialized core.extension config from DB during 500-error diagnosis.

4. Created sanitized SQL import file [skillsregistry.fixed.sql](skillsregistry.fixed.sql).
- Copy of original dump without the first MariaDB sandbox directive line, to support local mysql import client behavior.

## XAMPP/OS-Level Changes (Outside Workspace)
1. Updated XAMPP PHP configuration.
- Enabled gd extension.
- Enabled intl extension.
- zip extension DLL was not present in this XAMPP build, so zip could not be enabled.

2. Updated Apache virtual hosts configuration.
- Added a VirtualHost for skillsregistry.local with document root pointing to the project web directory.

3. Database bootstrap and import.
- Created database: skillsregistry.
- Ensured DB user exists and matches settings: webmaster / anything_you_wish_to_be.
- Granted privileges on skillsregistry.*.
- Increased max_allowed_packet to allow large dump import.
- Imported SQL dump after removing incompatible first line.
- Verified import result: 419 tables.

## Troubleshooting Performed
1. Initial SQL import error: Unknown command '\-'.
- Cause: First line in dump used MariaDB sandbox syntax unsupported by local client.
- Fix: Removed first line and re-imported.

2. Import packet-size failure.
- Error: Got a packet bigger than max_allowed_packet.
- Fix: Increased max_allowed_packet and re-imported.

3. Drupal HTTP 500 after import.
- Symptom: Missing plugin field_item:private.
- Root issue was not missing code in repository; plugin provider module exists.
- Fix path:
  - Ran Drush with XAMPP PHP binary (not system PHP 8.5).
  - Rebuilt caches successfully.
- Verification after fix: site returned HTTP 200.

## Runtime Commands Used For Final Recovery
- C:/xampp/php/php.exe vendor/bin/drush status
- C:/xampp/php/php.exe vendor/bin/drush cr

## Important Operational Notes
1. Use XAMPP PHP for Drupal CLI tasks.
- Prefer C:/xampp/php/php.exe vendor/bin/drush ...
- Avoid using system PHP 8.5 for this Drupal 9 project.

2. Existing project warning retained.
- Composer operations may overwrite critical rewrite behavior in web .htaccess, so preserve your known-good backup process.

3. Solr and Memcache local behavior.
- Site can run without Solr configured, but search functionality/relevance is reduced or unavailable until Solr setup is complete.
- Local memcache is disabled by settings.local override; production memcache settings remain in main settings file.

## Current Run URL
- http://localhost/skillsregistry/web/

## Recommended Cleanup (Optional)
- Remove [core_extension.yml](core_extension.yml) if no longer needed.
- Remove [skillsregistry.fixed.sql](skillsregistry.fixed.sql) if you do not want a duplicate SQL file in workspace.
