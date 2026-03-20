# Phase 1: Moderator Role Management Feature

**Part of:** [Chronological Timeline](TIMELINE.md)  
**First phase** of implementation cycle  
**Leads to:** [Phase 2 – Candidate Approval](candidate-approval-feature.md)

Date: 2026-03-20
Module path: `web/modules/custom/moderator_access`

## Goal

Enable existing WG moderators to manage moderator access by granting/removing the `wg` role for other users.

## Implementation Summary

Custom module added: `moderator_access`

Files:
- `moderator_access.info.yml`
- `moderator_access.permissions.yml`
- `moderator_access.routing.yml`
- `moderator_access.links.menu.yml`
- `moderator_access.links.task.yml`
- `moderator_access.module`
- `src/Form/ModeratorAccessForm.php`

## Functional Behavior

- Adds permission: `manage moderator access`.
- Adds management route: `/moderator/manage-access`.
- Management form supports:
  - Grant moderator access by username/email.
  - List all current moderators (`wg` role).
  - Remove moderator access from selected users.
- Safety check:
  - Prevents current user from removing their own `wg` role from this page.

## Permission Assignment

- Install hook grants `manage moderator access` to role `wg`.

## Runtime Bug Fixed

Issue:
- `Call to undefined method ... entityTypeManager()` on form render.

Fix:
- Injected `entity_type.manager` into `ModeratorAccessForm`.
- Replaced invalid `entityTypeManager()` calls with injected service property.

## Navigation

Primary URL:
- `http://localhost/moderator/manage-access`

Admin path target:
- Manage -> People -> Manage moderator access

## Validation Performed

- Module enabled successfully.
- Route registered.
- Permission exists on `wg`.
- Access behavior verified:
  - Non-WG denied.
  - WG allowed.
  - After revoke, denied again.
