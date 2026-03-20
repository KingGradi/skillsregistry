<?php
$uids = \Drupal::entityQuery('user')
  ->condition('roles', 'candidate')
  ->condition('field_candidate_approval_status', 'pending')
  ->accessCheck(FALSE)
  ->execute();

echo 'Pending candidates: ' . count($uids) . "\n";

if (!empty($uids)) {
  $storage = \Drupal::entityTypeManager()->getStorage('user');
  foreach ($storage->loadMultiple($uids) as $u) {
    echo sprintf(
      "UID:%d user:%s status:%s approval:%s delete_on:%s\n",
      $u->id(),
      $u->getAccountName(),
      $u->isActive() ? 'active' : 'blocked',
      $u->get('field_candidate_approval_status')->value ?: '(empty)',
      $u->get('field_candidate_delete_on')->value ?: '(none)'
    );
  }
}
