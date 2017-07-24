<?php

/**
 * @file
 * Drush commands for web_page_archive.
 */

/**
 * Implements web_page_archive_drush_command().
 */
function web_page_archive_drush_command() {
  $items = [];

  $items['web-page-archive-prepare-uninstall'] = [
    'description' => 'Prepares the web page archive for uninstalling.',
    'aliases' => ['wpa-pu'],
    'drupal dependencies' => ['web_page_archive'],
    'options' => [],
  ];

  return $items;
}

/**
 * Drush callback to prepare for uninstall.
 */
function drush_web_page_archive_prepare_uninstall() {
  if (drush_confirm('Are you sure you want to delete the entities?')) {
    $batch = [
      'title' => t('Prepare for uninstall.'),
      'operations' => [
        [
          'Drupal\web_page_archive\Controller\PrepareUninstallController::deleteRunEntities', [],
        ],
        [
          'Drupal\web_page_archive\Controller\PrepareUninstallController::removeFields', [],
        ],
      ],
      'progress_message' => t('Deleting web_page_archive data... Completed @percentage% (@current of @total).'),
    ];
    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();
  }
  else {
    drush_user_abort();
  }
}
