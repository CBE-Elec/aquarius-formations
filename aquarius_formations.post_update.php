<?php

use Drupal\Core\Config\FileStorage;

/**
 * Synchronise les vues gerees par le module.
 */
function aquarius_formations_post_update_sync_views_config(&$sandbox = NULL): void {
  _aquarius_formations_post_update_sync_views_config();
}

/**
 * Importe les vues gerees par le module depuis config/optional.
 */
function _aquarius_formations_post_update_sync_views_config(): void {
  $module_path = \Drupal::service('extension.list.module')->getPath('aquarius_formations');
  $default_config = new FileStorage(DRUPAL_ROOT . '/' . $module_path . '/config/optional');

  $config_names = [
    'views.view.niveaux_ffessm',
    'views.view.formation_membres',
    'views.view.formation',
  ];

  foreach ($config_names as $config_name) {
    $data = $default_config->read($config_name);
    if (!is_array($data) || $data === []) {
      continue;
    }

    $active = \Drupal::configFactory()->getEditable($config_name);
    $uuid = $active->get('uuid');
    if (!$active->isNew() && $uuid) {
      $data['uuid'] = $uuid;
    }

    $active->setData($data)->save(TRUE);
  }
}
