<?php

namespace Drupal\web_page_archive\Commands;

use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class WebPageArchiveCommands extends DrushCommands {

  /**
   * Trigger a capture for a web page archive config entity.
   *
   * @param string $config_entity
   *   The config entity on which to start a capture.
   * @param array $options
   *   Array of options whose values come from cli, aliases, config, etc.
   *
   * @option all
   *   Captures on all config entities.
   * @validate-module-enabled web_page_archive
   *
   * @command web-page-archive:capture
   * @aliases wpa:c,wpa-c,web-page-archive-capture
   */
  public function capture($config_entity = NULL, array $options = ['all' => NULL]) {
    if (!isset($config_entity) && !$options['all']) {
      throw new \Exception(dt('You must either specify one or more config entities or use the --all flag.'));
    }

    $ids = isset($config_entity) ? [$config_entity] : NULL;
    $cron_runner = \Drupal::getContainer()->get('web_page_archive.cron.runner');
    $config_entities = \Drupal::entityTypeManager()->getStorage('web_page_archive')->loadMultiple($ids);
    if (!empty($config_entities)) {
      foreach ($config_entities as $id => $config_entity) {
        $config_entity->startNewRun();
      }
      \drush_backend_batch_process();
    }
    elseif (is_array($ids)) {
      $this->output()->writeln(dt('Could not find any config entities matching "@config_entity".', ['@config_entity' => implode('|', $ids)]));
    }
    else {
      $this->output()->writeln(dt('Could not find any config entities.'));
    }
  }

  /**
   * Prepares the web page archive for uninstalling.
   *
   * @validate-module-enabled web_page_archive
   *
   * @command web-page-archive:prepare-uninstall
   * @aliases wpa:pu,wpa-pu,web-page-archive-prepare-uninstall
   */
  public function prepareUninstall() {
    if ($this->io()->confirm('Are you sure you want to delete the entities?')) {
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
      \drush_backend_batch_process();
    }
    else {
      throw new UserAbortException();
    }
  }

}
