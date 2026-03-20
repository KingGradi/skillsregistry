# Quick Commands

Date: 2026-03-20
Environment: XAMPP local Drupal (`http://localhost`)
Project root: `C:\xampp\htdocs\skillsregistry`

## 1. Core Drush Pattern (Windows/XAMPP)

Use this command structure for all Drupal CLI actions:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost <command>
```

Examples:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost status
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost cr
```

## 2. Cache Rebuild / Render Changes

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost cr
```

After running, hard refresh browser:
- `Ctrl+F5`

## 3. Module Enable / Check

Enable module:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost en -y moderator_access
```

Check enabled module list for moderator module:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost pml --status=enabled --type=module
```

## 4. Logs / Errors

Show recent errors:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost ws --count=20 --severity=Error
```

## 5. CAPTCHA (Localhost Testing)

Disable CAPTCHA on login:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost cset -y captcha.captcha_point.user_login_form status 0
```

Disable CAPTCHA on registration:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost cset -y captcha.captcha_point.user_register_form status 0
```

## 6. User and Role Testing

Create test user:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost user:create mod_access_test --mail="mod_access_test@example.com" --password="ModTest#2026"
```

Set password for existing user:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\skillsregistry\vendor\bin\drush --root=C:\xampp\htdocs\skillsregistry\web --uri=http://localhost user:password Admin "TempAdmin#2026"
```

## 7. MySQL Quick Checks (roles/routes/menu)

Open MySQL client from XAMPP:

```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u webmaster -panything_you_wish_to_be skillsregistry
```

List roles:

```sql
SELECT name FROM config WHERE name LIKE 'user.role.%' ORDER BY name;
```

Check moderator route:

```sql
SELECT path, name FROM router WHERE path='/moderator/manage-access';
```

Check menu entry placement:

```sql
SELECT id, menu_name, route_name, parent, enabled
FROM menu_tree
WHERE id='moderator_access.manage';
```

## 8. MySQL Packet Size Fix

Runtime set to 256MB:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root skillsregistry -e "SET GLOBAL max_allowed_packet=268435456;"
C:\xampp\mysql\bin\mysql.exe -u root skillsregistry -e "SHOW GLOBAL VARIABLES LIKE 'max_allowed_packet';"
```

Persistent setting file:
- `C:\xampp\mysql\bin\my.ini`
- Set under `[mysqld]`:

```ini
max_allowed_packet=256M
```

## 9. MailHog Quick Start

Start MailHog:

```powershell
Start-Process -FilePath "C:\mailhog\MailHog.exe" -WindowStyle Minimized
```

Check ports:

```powershell
Test-NetConnection -ComputerName localhost -Port 1025
Test-NetConnection -ComputerName localhost -Port 8025
```

Open UI:
- `http://localhost:8025`

## 10. Key URLs

- Login: `http://localhost/user/login`
- Moderator access: `http://localhost/moderator/manage-access`
- Moderator list: `http://localhost/moderator-list`
- People admin: `http://localhost/admin/people`
- Candidate registration: `http://localhost/candidate-registration`
- MailHog UI: `http://localhost:8025`
