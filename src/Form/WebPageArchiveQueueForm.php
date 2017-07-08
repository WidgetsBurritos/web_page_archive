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

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Submitting this form will process the web page archive queue which contains @number items.', ['@number' => $web_page_archive->getQueueCt()]),
    ];

    return $form;
  }

  /**
   * Processes the queue.
   */
  public function processQueue(array $form, FormStateInterface $form_state) {
    $web_page_archive = $this->getEntity();
    $queue = $this->queueFactory->get("web_page_archive_capture.{$web_page_archive->uuid()}");
    $queue_worker = $this->queueManager->createInstance("web_page_archive_capture");

    while ($item = $queue->claimItem()) {
      try {
        $queue_worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      catch (SuspendQueueException $e) {
        $queue->releaseItem($item);
        break;
      }
      catch (\Exception $e) {
        // TODO: What to do here? (future task)
        drupal_set_message($e->getMessage(), 'warning');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['process'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process Queue'),
      '#submit' => ['::processQueue'],
    ];
    return $actions;
  }

}
