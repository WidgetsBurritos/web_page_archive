<?php

/**
 * @file
 * Post Update commands for web_page_archive.
 */

use Drupal\Core\Config\FileStorage;
use Drupal\web_page_archive\Controller\RunComparisonController;

/**
 * Issue 2937227: Normalizes compare response variances to database.
 */
function web_page_archive_post_update_2937227_normalize_compare_response_variances(&$sandbox) {
  if (!isset($sandbox['total'])) {
    $sandbox['total'] = \Drupal::entityQuery('wpa_run_comparison')->count()->execute();
    if ($sandbox['total'] == 0) {
      return;
    }
    $sandbox['progress'] = 0;
  }

  // Retrieve batch of comparison IDs.
  $limit = 25;
  $query = \Drupal::entityQuery('wpa_run_comparison');
  $query->range($sandbox['progress'], $limit);
  $ids = $query->execute();

  // Retrieve comparisons.
  $comparisons = \Drupal::entityTypeManager()->getStorage('wpa_run_comparison')->loadMultiple($ids);
  foreach ($comparisons as $comparison) {
    $responses = $comparison->getResults();
    foreach ($responses as $response) {
      $unserialized = unserialize($response['results']);
      RunComparisonController::normalizeCompareResponseData($response['cid'], $unserialized['compare_response']);
    }

    $sandbox['progress']++;
  }

  \Drupal::messenger()->addStatus($sandbox['progress'] . ' run comparisons processed out of ' . $sandbox['total']);
  $sandbox['#finished'] = ($sandbox['progress'] / $sandbox['total']);
}

/**
 * Issue 2937227: Reimports the run comparison summary view.
 */
function web_page_archive_post_update_2937227_reimport_run_comparison_summary_view() {
  $views = ['views.view.web_page_archive_run_comparison_summary'];
  _web_page_archive_reimport_views($views);
}

/**
 * Issue 2956141: Reimports the web page archive canonical view.
 */
function web_page_archive_post_update_2956141_reimport_canonical_view() {
  $views = ['views.view.web_page_archive_canonical'];
  _web_page_archive_reimport_views($views);
}

/**
 * Issue 3072289: Reimports the web page archive canonical view.
 */
function web_page_archive_post_update_3072289_reimport_canonical_view() {
  $views = ['views.view.web_page_archive_canonical'];
  _web_page_archive_reimport_views($views);
}

/**
 * Sets data retention default settings for all existing config entities.
 */
function web_page_archive_post_update_3072289_set_default_retention_values() {
  $config_factory = \Drupal::service('config.factory');
  $config_prefix = 'web_page_archive.web_page_archive';
  $keys = $config_factory->listAll($config_prefix);

  foreach ($keys as $key) {
    $wpa_config = $config_factory->getEditable($key);
    // Defaulting to FALSE preserves existing functionality.
    $wpa_config->set('retention_type', '');
    $wpa_config->set('retention_value', '365');
    $wpa_config->save();
  }
}

/**
 * Issue 3011579: Reimports the web page archive canonical view.
 */
function web_page_archive_post_update_3011579_reimport_canonical_view() {
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

/**
 * Issue 3157902: Populate all run uuids.
 */
function web_page_archive_post_update_3157902_populate_run_uuids(&$sandbox) {
  $storage = \Drupal::entityTypeManager()->getStorage('web_page_archive_run');
  if (!isset($sandbox['list'])) {
    $sandbox['list'] = array_keys($storage->fullRevisionList());
    $sandbox['total'] = count($sandbox['list']);
    // If there are no items, there is nothing left to do here.
    if ($sandbox['total'] === 0) {
      return;
    }
    $sandbox['progress'] = 0;
  }

  // Get next 25 ids.
  $limit = 25;
  $ids = array_splice($sandbox['list'], 0, $limit);

  if (empty($ids)) {
    $sandbox['progress']++;
  }
  else {
    $revisions = $storage->loadMultipleRevisions($ids);
    foreach ($revisions as $revision) {
      $revision->set('run_uuid', $revision->getRunUuid());
      $revision->save();
      $sandbox['progress']++;
    }
  }

  \Drupal::messenger()->addStatus("{$sandbox['progress']}/{$sandbox['total']} results processed.");

  $sandbox['#finished'] = ($sandbox['progress'] / $sandbox['total']);
}

/**
 * Issue 3157902: Reimport the web_page_archive_individual view.
 */
function web_page_archive_post_update_3157902_reimport_view() {
  $views = ['views.view.web_page_archive_individual'];
  _web_page_archive_reimport_views($views);
}
