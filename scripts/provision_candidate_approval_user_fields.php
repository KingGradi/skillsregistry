<?php

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\Entity\Role;

if (!FieldStorageConfig::loadByName('user', 'field_candidate_approval_status')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_candidate_approval_status',
    'entity_type' => 'user',
    'type'        => 'list_string',
    'cardinality' => 1,
    'settings'    => [
      'allowed_values' => [
        'pending'  => 'Pending approval',
        'approved' => 'Approved',
        'declined' => 'Declined',
      ],
    ],
  ])->save();
  echo "Created field storage: field_candidate_approval_status\n";
}

if (!FieldConfig::loadByName('user', 'user', 'field_candidate_approval_status')) {
  FieldConfig::create([
    'field_name'  => 'field_candidate_approval_status',
    'entity_type' => 'user',
    'bundle'      => 'user',
    'label'       => 'Candidate Approval Status',
    'required'    => FALSE,
  ])->save();
  echo "Created field config: field_candidate_approval_status\n";
}

if (!FieldStorageConfig::loadByName('user', 'field_candidate_delete_on')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_candidate_delete_on',
    'entity_type' => 'user',
    'type'        => 'string',
    'cardinality' => 1,
    'settings'    => ['max_length' => 10],
  ])->save();
  echo "Created field storage: field_candidate_delete_on\n";
}

if (!FieldConfig::loadByName('user', 'user', 'field_candidate_delete_on')) {
  FieldConfig::create([
    'field_name'  => 'field_candidate_delete_on',
    'entity_type' => 'user',
    'bundle'      => 'user',
    'label'       => 'Candidate Deletion Scheduled',
    'required'    => FALSE,
  ])->save();
  echo "Created field config: field_candidate_delete_on\n";
}

$wg = Role::load('wg');
if ($wg && !$wg->hasPermission('manage candidate approvals')) {
  $wg->grantPermission('manage candidate approvals');
  $wg->save();
  echo "Granted permission to wg role\n";
}

$now = \Drupal::time()->getCurrentTime();
$one_day_ago = $now - 86400;

$uids = \Drupal::entityQuery('user')
  ->condition('roles', 'candidate')
  ->accessCheck(FALSE)
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('user');
$users = $storage->loadMultiple($uids);
foreach ($users as $user) {
  $status_empty = $user->get('field_candidate_approval_status')->isEmpty();
  if ($status_empty) {
    if ((int) $user->getCreatedTime() >= $one_day_ago) {
      $user->set('field_candidate_approval_status', 'pending');
      $user->set('field_candidate_delete_on', NULL);
      $user->block();
      echo "Set pending: UID {$user->id()} {$user->getAccountName()}\n";
    }
    else {
      $user->set('field_candidate_approval_status', 'approved');
      $user->set('field_candidate_delete_on', NULL);
      echo "Set approved (legacy): UID {$user->id()} {$user->getAccountName()}\n";
    }
    $user->save();
  }
}
