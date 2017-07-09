<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveQueueForm extends EntityForm {

  /**
   * Queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Queue worker mananager interface.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueueFactory $queue, QueueWorkerManagerInterface $queue_manager) {
    $this->queueFactory = $queue;
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
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $web_page_archive = $this->getEntity();
    $this->queueCt = $web_page_archive->getQueueCt();
    // TODO: Provide better instructions for these forms.
    if ($this->queueCt > 0) {
      $form['help'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Submitting this form will process the web page archive queue which contains @number items.', ['@number' => $web_page_archive->getQueueCt()]),
      ];
    }
    else {
      $form['help'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Click to start.'),
      ];
    }

    return $form;
  }

  /**
   * Processes the queue.
   */
  public function processQueue(array $form, FormStateInterface $form_state) {
    // TODO: Move this behavior to controller.
    // TODO: Add batch processing.
    $web_page_archive = $this->getEntity();
    $queue = $this->queueFactory->get("web_page_archive_capture.{$web_page_archive->uuid()}");
    $queue_worker = $this->queueManager->createInstance("web_page_archive_capture");

    $processed_ct = 0;
    while ($item = $queue->claimItem()) {
      try {
        $queue_worker->processItem($item->data);
        $queue->deleteItem($item);
        $processed_ct++;
      }
      catch (SuspendQueueException $e) {
        $queue->releaseItem($item);
        watchdog_exception('web_page_archive', $e);
        break;
      }
      catch (\Exception $e) {
        watchdog_exception('web_page_archive', $e);
      }
    }

    // TODO: What happens if there were exceptions above?
    drupal_set_message(t('@processed_ct jobs have been processed.', ['@processed_ct' => $processed_ct]), 'status');
    $form_state->setRedirect('entity.web_page_archive.canonical', ['web_page_archive' => $web_page_archive->id()]);
  }

  /**
   * Starts a new run.
   */
  public function startRun(array $form, FormStateInterface $form_state) {
    $web_page_archive = $this->getEntity();
    $web_page_archive->startNewRun();

    // TODO: Should there be some sort of validation the aboved worked?
    drupal_set_message(t('@label archive has been queued.', ['@label' => $web_page_archive->label()]), 'status');
    $form_state->setRedirect('entity.web_page_archive.canonical', ['web_page_archive' => $web_page_archive->id()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    if ($this->queueCt > 0) {
      $actions['process'] = [
        '#type' => 'submit',
        '#value' => $this->t('Process Queue'),
        '#submit' => ['::processQueue'],
      ];
    }
    else {
      $actions['process'] = [
        '#type' => 'submit',
        '#value' => $this->t('Start Run'),
        '#submit' => ['::startRun'],
      ];
    }
    return $actions;
  }

}
