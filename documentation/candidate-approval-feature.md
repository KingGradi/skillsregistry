# Phase 2: Candidate Approval Workflow

**Part of:** [Chronological Timeline](TIMELINE.md)  
**Follows:** [Phase 1 – Moderator Access](moderator-feature.md)  
**Leads to:** [Phase 3 – Login 500 Fix](login-500-fix.md)

## Date
- 2026-03-20

## Scope Implemented
- Candidate registrations now require moderator approval before they are considered approved.
- A moderator queue page is available to approve or decline candidate registrations.
- Approval sends the required approval email message.
- Decline sends the required rejection email message.
- Declined candidates are marked for deletion after 30 days.
- If a declined candidate amends/resubmits during the 30-day period, the delete flag is cleared and status returns to pending.

## Important Technical Finding
- In this Skills Registry setup, candidate registration creates candidate user accounts.
- It does not create student nodes in the current registration path.
- The workflow was therefore implemented against user accounts with the candidate role.

## New Module
- Module: candidate_approval
- Path: web/modules/custom/candidate_approval

## Files Added/Updated
- web/modules/custom/candidate_approval/candidate_approval.info.yml
- web/modules/custom/candidate_approval/candidate_approval.permissions.yml
- web/modules/custom/candidate_approval/candidate_approval.routing.yml
- web/modules/custom/candidate_approval/candidate_approval.links.menu.yml
- web/modules/custom/candidate_approval/candidate_approval.module
- web/modules/custom/candidate_approval/candidate_approval.install
- web/modules/custom/candidate_approval/src/Form/CandidateApprovalForm.php

## Route / Access
- Moderator queue page:
  - /admin/people/candidate-approvals
- Permission used:
  - manage candidate approvals
- WG role is granted this permission by module logic.

## Candidate Status Fields
Fields are stored on user accounts (bundle user):
- field_candidate_approval_status
  - Values: pending, approved, declined
- field_candidate_delete_on
  - Date string format: YYYY-MM-DD

## Workflow Logic

### On candidate registration
- If a user has role candidate and is newly created:
  - approval status is set to pending
  - delete flag is cleared
  - account is blocked until moderator decision

### On moderator approve
- Candidate status changes to approved
- Delete flag is cleared
- Candidate account is activated
- Approval email is sent

### On moderator decline
- Candidate status changes to declined
- Delete flag is set to current date + 30 days
- Candidate account remains active for appeal/resubmission path
- Rejection email is sent

### On candidate resubmission after decline
- If non-moderator updates a declined candidate profile/account:
  - status is reset to pending
  - delete flag is cleared
  - account is blocked again pending moderator decision

### Scheduled cleanup (cron)
- Candidates with status declined and delete date <= today are deleted.

## Email Templates Implemented

### Approval email
- Subject: Registration for the ICT Skills Registry approved
- Body starts with:
  - Dear [candidate],
  - Thank you for registering on the ICT Skills Registry. Your registration has been approved and will now be visible to companies using the system.

### Rejection email
- Subject: Registration for the ICT Skills Registry rejected
- Body starts with:
  - Dear [candidate],
  - Your registration for the ICT Skills Register has been rejected.

## Verification Performed
- Verified that candidate approval fields exist on user accounts.
- Verified that the latest newly registered candidate was set to:
  - status: pending
  - account: blocked
- Verified moderator queue route is available and requires login/permission.

## Notes / Environment Caveat
- drush updb had an environment-specific PHP composer platform mismatch in one execution path.
- Equivalent provisioning was applied via drush php:script to ensure fields and behavior were active immediately.
