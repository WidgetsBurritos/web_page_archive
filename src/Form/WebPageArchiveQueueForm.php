<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
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
   * Indicates all necessary dependencies are installed.
   *
   * @var bool
   */
  protected $missingDependencies = [];

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

    // Look for any missing dependencies.
    $utilities = $web_page_archive->getCaptureUtilities()->getInstanceIds();
    foreach ($utilities as $utility) {
      $utility_instance = $web_page_archive->getCaptureUtility($utility);
      $missing_dependencies = $utility_instance->missingDependencies();
      if (!empty($missing_dependencies)) {
        $this->missingDependencies = $this->missingDependencies + $missing_dependencies;
      }
    }

    // If there are no missing items give instructions on starting a run.
    $missing_ct = count($this->missingDependencies);
    if ($missing_ct === 0) {
      $url = Url::fromUri($web_page_archive->getSitemapUrl());
      $url->setOptions(['attributes' => ['target' => '_blank']]);
      $values = ['@sitemap_url' => Link::fromTextAndUrl($web_page_archive->getSitemapUrl(), $url)->toString()];
      $form['help'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Click "Start Run" to capture all urls from @sitemap_url.', $values),
      ];
    }
    // If there are missing dependencies, display message.
    else {
      drupal_set_message($this->t("Capture job can't run at this time!"), 'error');
      $form['help'] = [
        '#theme' => 'item_list',
        '#items' => $this->missingDependencies,
        '#prefix' => $this->formatPlural($missing_ct, 'Missing dependency:', 'Missing dependencies:'),
      ];
    }

    return $form;
  }

  /**
   * Starts a new run.
   */
  public function startRun(array $form, FormStateInterface $form_state) {
    $web_page_archive = $this->getEntity();
    $web_page_archive->startNewRun();

    $queue = $web_page_archive->getQueue();
    $queue_worker = $this->queueManager->createInstance('web_page_archive_capture');

    // Create capture job batch.
    $batch = [
      'title' => $this->t('Process all capture queue jobs with batch'),
      'operations' => [],
      'finished' => 'Drupal\web_page_archive\Controller\WebPageArchiveController::batchFinished',
    ];

    // Create batch operations.
    for ($i = 0; $i < $queue->numberOfItems(); $i++) {
      $batch['operations'][] = ['Drupal\web_page_archive\Controller\WebPageArchiveController::batchProcess', [$web_page_archive]];
    }

    // Adds the batch sets.
    batch_set($batch);

    // TODO: Should there be some sort of validation the aboved worked?
    $form_state->setRedirect('entity.web_page_archive.canonical', ['web_page_archive' => $web_page_archive->id()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = [];
    if (empty($this->missingDependencies)) {
      $actions['start'] = [
        '#type' => 'submit',
        '#value' => $this->t('Start Run'),
        '#submit' => ['::startRun'],
      ];
    }
    return $actions;
  }

}
