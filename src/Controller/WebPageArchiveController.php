<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\RequeueException;
use Drupal\views\Views;
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
    $view = Views::getView('web_page_archive_canonical');
    if (!isset($view)) {
      // TODO: What to do here? If this happens, it means someone deleted the
      // view that got installed when the module was enabled. Should we display
      // some sort of message requesting they either reimport it, or try to
      // automatically re-import it? Leaving this feedback here to resolve at
      // a later time.
      throw new \Exception("View not found!");
    }
    $run_entity = $web_page_archive->getRunEntity();
    if (!isset($run_entity)) {
      // TODO: What to do here? This is actually something we can correct.
      // If a run entity does not exist for a config entity, we could generate
      // one and then try again. That said, that may be indicative of a larger
      // problem at which point we're just masking the error instead of
      // correcting it. One case this may happen is if a user "Prepares for
      // Uninstall" and then doesn't actually initiate an uninstall.
      // Leaving this feedback here to resolve at a later time.
      throw new \Exception("Missing run entity");
    }

    $view->setDisplay('canonical_embed');
    $view->setArguments([$run_entity->id()]);
    return $view->render();
  }

  /**
   * Returns title of the archive.
   */
  public function title($web_page_archive) {
    return $web_page_archive->label();
  }

  /**
   * Adds up to $items_to_process number of items from the queue to a batch.
   *
   * If $items_to_process < 0 attempt to add entire queue to batch.
   */
  public static function setBatch(WebPageArchive $web_page_archive, $items_to_process = -1) {
    $queue = $web_page_archive->getQueue();
    $queue_worker = \Drupal::service('plugin.manager.queue_worker')->createInstance('web_page_archive_capture');

    // Create capture job batch.
    $batch = [
      'title' => \t('Process all capture queue jobs with batch'),
      'operations' => [],
      'finished' => 'Drupal\web_page_archive\Controller\WebPageArchiveController::batchFinished',
    ];

    // If negative, or if count is too high, set count to queue size.
    if ($items_to_process < 0 || $items_to_process > $queue->numberOfItems()) {
      $items_to_process = $queue->numberOfItems();
    }

    // Create batch operations.
    for ($i = 0; $i < $items_to_process; $i++) {
      $batch['operations'][] = ['Drupal\web_page_archive\Controller\WebPageArchiveController::batchProcess', [$web_page_archive]];
    }

    // Adds the batch sets.
    batch_set($batch);
  }

  /**
   * Common batch processing callback for all operations.
   */
  public static function batchProcess(WebPageArchive $web_page_archive, &$context) {
    $queue = $web_page_archive->getQueue();
    $queue_worker = \Drupal::service('plugin.manager.queue_worker')->createInstance('web_page_archive_capture');

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
      }
      catch (\Exception $e) {
        // In case of any other kind of exception, log it and leave the item
        // in the queue to be processed again later.
        watchdog_exception('cron', $e);
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
