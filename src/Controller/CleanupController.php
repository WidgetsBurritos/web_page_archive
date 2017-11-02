<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\RequeueException;

/**
 * Class WebPageArchiveController.
 *
 * @package Drupal\web_page_archive\Controller
 */
class CleanupController extends ControllerBase {

  /**
   * Queues all revisions of a run entity up for file cleanup.
   */
  public static function cleanRunEntity($web_page_archive_run) {
    // Retrieve relevant capture utilities.
    $web_page_archive = $web_page_archive_run->getConfigEntity();
    $utilities = $web_page_archive->getCaptureUtilities();

    // Retrieve revision list.
    $web_page_archive_run_storage = \Drupal::service('entity_type.manager')->getStorage('web_page_archive_run');
    $vids = $web_page_archive_run_storage->revisionIds($web_page_archive_run);

    // Cleanup all utilities for all revisions.
    foreach ($utilities as $utility) {
      foreach ($vids as $vid) {
        $utility->cleanupRevision($vid);
      }
      $utility->cleanupEntity($web_page_archive->id());
    }
  }

  /**
   * Queues a file for deletion.
   */
  public static function queueFileDelete($file) {
    $queue = \Drupal::service('queue')->get('web_page_archive_cleanup');
    $queue->createItem(['type' => 'file', 'path' => $file]);
  }

  /**
   * Queues a directory for deletion.
   */
  public static function queueDirectoryDelete($path) {
    $queue = \Drupal::service('queue')->get('web_page_archive_cleanup');
    $queue->createItem(['type' => 'directory', 'path' => $path]);
  }

  /**
   * Processes up to $items_to_process cleanup tasks.
   */
  public static function processCleanup($items_to_process) {
    $queue = \Drupal::service('queue')->get('web_page_archive_cleanup');
    // If negative, or if count is too high, set count to queue size.
    if ($items_to_process < 0 || $items_to_process > $queue->numberOfItems()) {
      $items_to_process = $queue->numberOfItems();
    }
    for ($i = 0; $i < $items_to_process; $i++) {
      static::processCleanupNextItem();
    }
  }

  /**
   * Common batch processing callback for all operations.
   */
  private static function processCleanupNextItem() {
    $queue = \Drupal::service('queue')->get('web_page_archive_cleanup');
    $queue_worker = \Drupal::service('plugin.manager.queue_worker')->createInstance('web_page_archive_cleanup');

    if ($item = $queue->claimItem()) {
      try {
        $processed = $queue_worker->processItem($item->data);
        if (!isset($processed)) {
          throw new RequeueException(t('Still Running'));
        }
        $queue->deleteItem($item);
        return TRUE;
      }
      catch (RequeueException $e) {
        $queue->releaseItem($item);
      }
      catch (SuspendQueueException $e) {
        $queue->releaseItem($item);
        watchdog_exception($e);
      }
      catch (\Exception $e) {
        // In case of any other kind of exception, log it and leave the item
        // in the queue to be processed again later.
        watchdog_exception('cron', $e);
      }
    }

    return FALSE;
  }

}
