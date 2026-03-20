<?php

try {
  $route = \Drupal::service('router.route_provider')->getRouteByName('candidate_approval.pending_list');
  echo 'OK:' . $route->getPath();
}
catch (\Exception $e) {
  echo 'ERR:' . $e->getMessage();
}
