<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Url;
use Drupal\views\Views;
use Drupal\web_page_archive\Entity\WebPageArchive;
use Drupal\web_page_archive\Event\CaptureJobCompleteEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveController.
 *
 * @package Drupal\web_page_archive\Controller
 */
class WebPageArchiveController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
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
  public static function batchProcess(WebPageArchive $web_page_archive, &$context = NULL) {
    if (empty($context['results']['entity'])) {
      $context['results']['entity'] = $web_page_archive;
    }
    $queue = $web_page_archive->getQueue();
    $queue_worker = \Drupal::service('plugin.manager.queue_worker')->createInstance('web_page_archive_capture');

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
        // In case of any other kind of exception, log it and remove it from
        // the queue to prevent queues from getting stuck.
        $queue->deleteItem($item);
        watchdog_exception('cron', $e);
      }

      return FALSE;
    }

  }

  /**
   * Batch finished callback.
   */
  public static function batchFinished($success, $results, $operations) {
    if ($success) {
      // Dispatch an event.
      if (isset($results['entity'])) {
        $run_entity = $results['entity']->getRunEntity();
        $event = new CaptureJobCompleteEvent($run_entity);
        $event_dispatcher = \Drupal::service('event_dispatcher');
        $event_dispatcher->dispatch($event::EVENT_NAME, $event);
      }
    }
    else {
      $error_operation = reset($operations);
      $values = [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ];
      \Drupal::messenger()->addError(t('An error occurred while processing @operation with arguments : @args', $values));
    }
  }

  /**
   * Ensures proper dependencies are installed in the system.
   */
  public static function checkDependencies() {
    if (!class_exists('\\Cron\\CronExpression')) {
      $urlObj = Url::fromUri('https://www.drupal.org/project/web_page_archive#installation');
      $urlObj->setOptions(['attributes' => ['target' => '_blank']]);
      $instructions_link = Link::fromTextAndUrl(t('installation instructions'), $urlObj)->toString();
      \Drupal::messenger()->addError(t('Missing mtdowling/cron-expression package. Web page archive must be installed via composer. See @instructions for more information.', ['@instructions' => $instructions_link]));
      return FALSE;
    }
    return TRUE;
  }

}
