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
