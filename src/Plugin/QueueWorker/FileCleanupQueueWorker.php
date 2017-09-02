<?php

namespace Drupal\web_page_archive\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Provides functionality for running capture jobs.
 *
 * @QueueWorker(
 *   id = "web_page_archive_cleanup",
 *   title = @Translation("Web Page Archive Cleanup"),
 * )
 */
class FileCleanupQueueWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (file_exists($data['path'])) {
      if (\Drupal::service('file_system')->unlink($data['path'])) {
        return TRUE;
      }
      else {
        throw new \Exception(t('Could not delete @file', ['@file' => $file]));
      }
    }
    return FALSE;
  }

}
