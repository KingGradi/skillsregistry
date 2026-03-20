# Phase 3: Login 500 Error Fix – March 20, 2026

**Part of:** [Chronological Timeline](TIMELINE.md)  
**Triggered by:** Candidate approval access hook implementation (Phase 2)  
**Status:** ✅ Fixed

## Problem

Users attempting to log in received a 500 Service Unavailable error. The issue occurred for all user roles during login redirect checks (URLs with `?check_logged_in=1` parameter).

## Root Cause

The candidate approval access hook (`candidate_approval_user_access()` in [candidate_approval.module](../web/modules/custom/candidate_approval/candidate_approval.module)) was calling `hasRole()` method on an `AccountInterface`/`AccountProxy` object during Drupal's routing access checks.

The `AccountProxy` class does not implement `hasRole()` directly, resulting in a fatal error:
```
Call to undefined method Drupal\Core\Session\AccountProxy::hasRole()
```

This happened during login when Drupal was redirecting authenticated users and checking node/user access permissions, triggering the access hook before the session was fully initialized.

## Solution

**File Modified:** `web/modules/custom/candidate_approval/candidate_approval.module` (line 163)

**Before:**
```php
if (!$account->hasRole('company')) {
  return \Drupal\Core\Access\AccessResult::neutral();
}
```

**After:**
```php
$viewer_roles = $account->getRoles();
if (!in_array('company', $viewer_roles, TRUE)) {
  return \Drupal\Core\Access\AccessResult::neutral();
}
```

### Why This Works

- `getRoles()` is always available on any `AccountInterface` implementation, including `AccountProxy`
- Returns an array of role IDs like `['authenticated', 'company']`
- Using `in_array()` with strict type checking (`TRUE`) is reliable and safe during all request phases
- No fatal error occurs; access checks complete normally

## Testing

Try logging in as any user (admin, candidate, company, etc.). The login should complete without 500 errors.

## Related Code

- **Access hook:** [candidate_approval_user_access()](../web/modules/custom/candidate_approval/candidate_approval.module#L157-L175)
- **Module:** `candidate_approval` – handles candidate registration moderation workflow
- **Scope:** Prevents company users from viewing non-approved candidate accounts

## Technical Note

This pattern (`getRoles() + in_array()`) should be used instead of `hasRole()` whenever the `$account` parameter comes from hook implementations that fire during request routing or authentication middleware, where the account object may not be fully initialized.

---

## Timeline Context

- **Previous phase:** [Phase 2 – Candidate Approval Workflow](candidate-approval-feature.md)
- **All work:** [Chronological Timeline](TIMELINE.md)

