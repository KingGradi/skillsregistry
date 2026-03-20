<?php
$uids = \Drupal::entityQuery('user')->condition('roles', 'candidate')->sort('created', 'DESC')->range(0, 5)->accessCheck(FALSE)->execute();
echo "Latest candidate users: " . count($uids) . "\n";
foreach (\Drupal\user\Entity\User::loadMultiple($uids) as $u) {
  echo sprintf("UID:%d name:%s status:%s created:%s\n", $u->id(), $u->getAccountName(), $u->isActive() ? 'active' : 'blocked', date('Y-m-d H:i:s', $u->getCreatedTime()));
}

$nids = \Drupal::entityQuery('node')->condition('type', 'student')->sort('created', 'DESC')->range(0, 5)->accessCheck(FALSE)->execute();
echo "\nLatest student nodes: " . count($nids) . "\n";
foreach (\Drupal\node\Entity\Node::loadMultiple($nids) as $n) {
  $owner = $n->getOwner();
  $appr = $n->hasField('field_approval_status') ? ($n->get('field_approval_status')->value ?: '(empty)') : '(no field)';
  echo sprintf("NID:%d owner:%s status:%s approval:%s created:%s\n", $n->id(), $owner ? $owner->getAccountName() : 'none', $n->isPublished() ? 'published' : 'unpublished', $appr, date('Y-m-d H:i:s', $n->getCreatedTime()));
}
