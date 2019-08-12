<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\RequeueException;

/**
 * Class WebPageArchiveController.
 *
 * @package Drupal\web_page_archive\Controller
 */
class CleanupController extends ControllerBase {

  /**
   * Removes all legacy revisions based on revision count.
   */
  public static function deleteOldRevisionsByDays($web_page_archive) {
    $run_storage = \Drupal::entityTypeManager()->getStorage('web_page_archive_run');
    $revisions = $web_page_archive->getRevisionIds();
    $keep_days = $web_page_archive->getRetentionValue();
    $utilities = $web_page_archive->getCaptureUtilities();
    $current_time = \Drupal::service('datetime.time')->getCurrentTime();
    $time_limit = $current_time - $keep_days * 86400;

    if ($keep_days < 0) {
      \Drupal::logger('web_page_archive')->warning(t('Data retention value is negative for the @name job. No results were removed.', ['@name' => $web_page_archive->label()]));
      return;
    }

    foreach ($revisions as $vid) {
      $run = $run_storage->loadRevision($vid);
      $created = $run->getRevisionCreationTime();

      // Skip revisions newer than time limit.
      if ($created > $time_limit) {
        continue;
      }

      // If we're not looking at the default or a locked revision, remove it.
      if (!$run->isDefaultRevision() && !$run->getRetentionLocked()) {
        $run_storage->deleteRevision($vid);
      }
    }

    Cache::invalidateTags(['config:views.view.web_page_archive_canonical']);
  }

  /**
   * Removes all legacy revisions based on revision count.
   */
  public static function deleteOldRevisionsByRevisions($web_page_archive) {
    $run_storage = \Drupal::entityTypeManager()->getStorage('web_page_archive_run');
    $revisions = $web_page_archive->getRevisionIds();
    $keep_revisions = $web_page_archive->getRetentionValue();
    $utilities = $web_page_archive->getCaptureUtilities();
    if ($keep_revisions < 0) {
      \Drupal::logger('web_page_archive')->warning(t('Data retention value is negative for the @name job. No results were removed.', ['@name' => $web_page_archive->label()]));
      return;
    }

    // Delete oldest revisions.
    $revision_ct = count($revisions);
    foreach ($revisions as $vid) {
      // If we have under the limit, then we don't need to do anything.
      if ($revision_ct <= $keep_revisions) {
        break;
      }
      $run = $run_storage->loadRevision($vid);
      // If we're not looking at the default or a locked revision, remove it.
      if (!$run->isDefaultRevision() && !$run->getRetentionLocked()) {
        $run_storage->deleteRevision($vid);
        $revision_ct--;
      }
    }

    Cache::invalidateTags(['config:views.view.web_page_archive_canonical']);
  }

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
