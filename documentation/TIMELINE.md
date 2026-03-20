# Skills Registry Development Timeline

## Chronological Progress of Work (March 2026)

This document tracks the features and fixes implemented in order, from first to latest.

---

## Phase 1: Moderator Access Governance

**When:** Early March 2026  
**What:** Implemented moderator role management system  
**Status:** ✅ Complete

### Key Features:
- Grant/revoke moderator status (`wg` role)
- Permission-based access control (`manage moderator access`)
- Admin form interface at `/moderator/manage-access`

### Files:
- [moderator-feature.md](moderator-feature.md) – Full implementation details
- Module location: `web/modules/custom/moderator_access/`

### Outcome:
Moderators can now manage other moderators without direct database access.

---

## Phase 2: Candidate Approval Workflow

**When:** Mid-Late March 2026  
**What:** Built candidate registration moderation system with email notifications  
**Status:** ✅ Complete (with fixes)

### Key Features:
- Candidates start in pending/blocked state on registration
- Moderator approval queue at `/admin/people/candidate-approvals`
- Approve/decline decision with email templates
- 30-day deletion grace period for declined candidates
- Company users cannot view non-approved candidates

### Files:
- [candidate-approval-feature.md](candidate-approval-feature.md) – Implementation guide
- Module location: `web/modules/custom/candidate_approval/`

### Issues Encountered & Fixed:
- **Issue:** Workflow was targeting student nodes instead of user entities
  - **Fix:** Refactored to user-based approval system
- **Issue:** Field name exceeded 32-character limit
  - **Fix:** Renamed `field_candidate_deletion_scheduled` → `field_candidate_delete_on`
- **Issue:** Access hook using unsupported `hasRole()` on AccountProxy
  - **Fix:** See Phase 3 (Login 500 Error Fix)

### Outcome:
Moderation of new candidate registrations now works end-to-end with proper blocking and email notifications.

---

## Phase 3: Login 500 Error Fix

**When:** March 20, 2026 (latest)  
**What:** Resolved fatal error preventing all users from logging in  
**Status:** ✅ Fixed

### The Problem:
- All login attempts returned 500 Service Unavailable
- Error URL: `?check_logged_in=1` parameter triggered the crash
- Root cause: Candidate approval access hook called `hasRole()` on `AccountProxy`

### The Solution:
**File:** `web/modules/custom/candidate_approval/candidate_approval.module` (line 163)

Changed:
```php
if (!$account->hasRole('company')) { ... }
```

To:
```php
$viewer_roles = $account->getRoles();
if (!in_array('company', $viewer_roles, TRUE)) { ... }
```

### Files:
- [login-500-fix.md](login-500-fix.md) – Technical details

### Outcome:
Login now works for all users without fatal errors.

---

## Quick Reference by Document

| Document | Phase | Purpose |
|----------|-------|---------|
| [moderator-feature.md](moderator-feature.md) | 1 | Moderator access system documentation |
| [candidate-approval-feature.md](candidate-approval-feature.md) | 2 | Candidate moderation workflow documentation |
| [login-500-fix.md](login-500-fix.md) | 3 | Login crash fix details |
| [local-dev-setup.md](local-dev-setup.md) | Baseline | Local XAMPP setup notes |
| [quick-commands.md](quick-commands.md) | Reference | Common development commands |
| [troubleshooting-log.md](troubleshooting-log.md) | Reference | Issue resolution reference |

---

## What To Test Next

1. **Login as different roles:** admin, candidate, company, wg (moderator)
2. **Moderator features:** Navigate to `/moderator/manage-access`, grant/revoke moderator role
3. **Candidate approval:** Go to `/admin/people/candidate-approvals`, view pending candidates, approve/decline
4. **Company visibility:** Log in as company user, verify non-approved candidates are hidden

---

## Key Lessons Learned

- **AccountInterface vs User entity:** Not all account objects have `hasRole()`. Use `getRoles() + in_array()` in hooks.
- **Registration entity mapping:** Verify what entity type stores new user data (here: user entity, not nodes).
- **Field naming limits:** Drupal machine names have a 32-character limit including prefix.
- **Module interactions:** Access hooks fire early in request lifecycle before session is fully ready.
