<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WebPageArchiveController.
 *
 * @package Drupal\web_page_archive\Controller
 */
class PrepareUninstallController extends ControllerBase {

  /**
   * Deletes web_page_archive subscribers.
   */
  public static function deleteRunEntities(&$context) {
    $storage = \Drupal::entityTypeManager()->getStorage('web_page_archive_run');
    $run_ids = $storage->getQuery()->range(0, 100)->execute();
    if ($run = $storage->loadMultiple($run_ids)) {
      $storage->delete($run);
    }
    $context['finished'] = (int) count($run_ids) < 100;
  }

  /**
   * Removes web_page_archive fields.
   */
  public static function removeFields() {
    $config = \Drupal::configFactory();
    $config->getEditable('field.field.web_page_archive_run.web_page_archive_run.field_captures')->delete();
    $config->getEditable('core.entity_form_display.web_page_archive_run.web_page_archive_run')->delete();
    $config->getEditable('field.storage.web_page_archive_run.field_captures')->delete();
  }

}
