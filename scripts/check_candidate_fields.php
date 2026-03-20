<?php
// Check if the two required fields exist on student nodes
$fields = [
  'field_approval_status' => 'Approval Status',
  'field_deletion_scheduled' => 'Deletion Scheduled',
];

foreach ($fields as $machine => $label) {
  $fc = \Drupal\field\Entity\FieldConfig::loadByName('node', 'student', $machine);
  echo $fc ? "✓ $label ($machine) exists\n" : "✗ $label ($machine) MISSING\n";
}

// Find the newest student node and check its status
$nids = \Drupal::entityQuery('node')
  ->condition('type', 'student')
  ->sort('created', 'DESC')
  ->range(0, 1)
  ->accessCheck(FALSE)
  ->execute();

if (!empty($nids)) {
  $nid = reset($nids);
  $node = \Drupal\node\Entity\Node::load($nid);
  
  echo "\nNewest student node (NID $nid):\n";
  echo "  Title: " . $node->getTitle() . "\n";
  echo "  Published: " . ($node->isPublished() ? 'Yes' : 'No') . "\n";
  echo "  Approval Status: " . ($node->get('field_approval_status')->value ?: 'NOT SET') . "\n";
  echo "  Deletion Scheduled: " . ($node->get('field_deletion_scheduled')->value ?: 'NOT SET') . "\n";
}
else {
  echo "\nNo student nodes found in system.\n";
}
