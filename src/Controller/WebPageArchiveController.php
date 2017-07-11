<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\RequeueException;
use Drupal\web_page_archive\Entity\WebPageArchive;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveController.
 *
 * @package Drupal\web_page_archive\Controller
 */
class WebPageArchiveController extends ControllerBase {

  /**
   * Drupal\Core\Queue\QueueFactory definition.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * Drupal\Core\Queue\QueueWorkerManagerInterface definition.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueManager;

  /**
   * Constructs a new WebPageArchiveController object.
   */
  public function __construct(QueueFactory $queue, QueueWorkerManagerInterface $queue_manager) {
    // TODO: Evaluate need for these.
    $this->queue = $queue;
    $this->queueManager = $queue_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('queue'),
      $container->get('plugin.manager.queue_worker')
    );
  }

  /**
   * Returns render array for displaying run history.
   */
  public function viewRuns($web_page_archive) {
    return [
      '#theme' => 'web_page_archive',
      '#test_var' => $this->t('Test Value'),
    ];
  }

  /**
   * Returns title of the archive.
   */
  public function title($web_page_archive) {
    return $web_page_archive->label();
  }

  /**
   * Common batch processing callback for all operations.
   */
  public static function batchProcess(WebPageArchive $web_page_archive, &$context) {
    $queue = $web_page_archive->getQueue();
    $queue_worker = \Drupal::service('plugin.manager.queue_worker')->createInstance('web_page_archive_capture');

    // TODO: Move threshold into admin panel.
    // Per-entity or global for all settings?
    $number_of_queue = ($queue->numberOfItems() < WEB_PAGE_ARCHIVE_BATCH_SIZE) ? $queue->numberOfItems() : WEB_PAGE_ARCHIVE_BATCH_SIZE;

    for ($i = 0; $i < $number_of_queue; $i++) {
      // Claim item and attempt to process it.
      if ($item = $queue->claimItem()) {
        try {
          $queue_worker->processItem($item->data);
          $queue->deleteItem($item);
        }
        catch (RequeueException $e) {
          $queue->releaseItem($item);
        }
        catch (SuspendQueueException $e) {
          $queue->releaseItem($item);
          watchdog_exception($e);
          break;
        }
        catch (\Exception $e) {
          // In case of any other kind of exception, log it and leave the item
          // in the queue to be processed again later.
          watchdog_exception('cron', $e);
        }
      }
    }
  }

  /**
   * Batch finished callback.
   */
  public static function batchFinished($success, $results, $operations) {
    if ($success) {
      drupal_set_message(t("The capture has been completed."));
    }
    else {
      $error_operation = reset($operations);
      $values = [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ];
      drupal_set_message(t('An error occurred while processing @operation with arguments : @args', $values));
    }
  }

}
