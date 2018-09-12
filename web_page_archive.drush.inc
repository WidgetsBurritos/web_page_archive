<?php

/**
 * @file
 * Drush commands for web_page_archive.
 */

/**
 * Implements web_page_archive_drush_command().
 */
function web_page_archive_drush_command() {
  $items = [
    'web-page-archive-capture' => [
      'description' => 'Trigger a capture for a web page archive config entity.',
      'aliases' => ['wpa-c'],
      'drupal dependencies' => ['web_page_archive'],
      'arguments' => [
        'config_entity' => 'The config entity on which to start a capture.',
      ],
      'options' => [
        'all' => 'Captures on all config entities.',
      ],
    ],
    'web-page-archive-prepare-uninstall' => [
      'description' => 'Prepares the web page archive for uninstalling.',
      'aliases' => ['wpa-pu'],
      'drupal dependencies' => ['web_page_archive'],
      'options' => [],
    ],
  ];

  return $items;
}

/**
 * Drush callback to capture config entities.
 */
function drush_web_page_archive_capture($config_entity = NULL) {
  if (!isset($config_entity) && !drush_get_option('all')) {
    return drush_set_error('DRUSH_COMMAND_NOT_EXECUTABLE', dt('You must either specify one or more config entities or use the --all flag.'));
  }

  $ids = isset($config_entity) ? func_get_args() : NULL;
  $cron_runner = \Drupal::getContainer()->get('web_page_archive.cron.runner');
  $config_entities = \Drupal::entityTypeManager()->getStorage('web_page_archive')->loadMultiple($ids);
  if (!empty($config_entities)) {
    foreach ($config_entities as $id => $config_entity) {
      $config_entity->startNewRun();
    }
    drush_backend_batch_process();
  }
  elseif (is_array($ids)) {
    \Drupal::messenger()->addStatus(dt('Could not find any config entities matching "@config_entity".', ['@config_entity' => implode('|', $ids)]));
  }
  else {
    \Drupal::messenger()->addStatus(dt('Could not find any config entities.'));
  }
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
