<?php

/**
 * @file
 * Post Update commands for web_page_archive.
 */

use Drupal\Core\Config\FileStorage;

/**
 * Issue 2956141: Reimports the web page archive canonical view.
 */
function web_page_archive_post_update_2956141_reimport_canonical_view() {
  $views = ['views.view.web_page_archive_canonical'];
  _web_page_archive_reimport_views($views);
}

/**
 * Helper function to reimport existing views from the install directory.
 */
function _web_page_archive_reimport_views($views) {
  $path = drupal_get_path('module', 'web_page_archive') . '/config/install';
  $source = new FileStorage($path);
  $config_storage = \Drupal::service('config.storage');
  foreach ($views as $view) {
    $config_storage->write($view, $source->read($view));
  }
}
