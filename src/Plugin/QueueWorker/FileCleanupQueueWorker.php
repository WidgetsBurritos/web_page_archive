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
      $logger = \Drupal::logger('web_page_archive');
      switch ($data['type']) {
        case 'file':
          if (\Drupal::service('file_system')->unlink($data['path'])) {
            $logger->notice(t('Deleted file @file', ['@file' => $data['path']]));
            return TRUE;
          }
          else {
            throw new \Exception(t('Could not delete @file', ['@file' => $data['path']]));
          }
        case 'directory':
          if (\file_unmanaged_delete_recursive($data['path'])) {
            $logger->notice(t('Deleted directory: @dir', ['@dir' => $data['path']]));
            return TRUE;
          }
          else {
            throw new \Exception(t('Could not delete @dir', ['@dir' => $data['path']]));
          }
      }
    }

    return FALSE;
  }

}
