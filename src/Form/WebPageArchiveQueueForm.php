<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveQueueForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveQueueForm extends EntityForm {

  use MessengerTrait;

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
      switch ($web_page_archive->getUrlType()) {
        case 'sitemap':
        case 'url':
          $list = [];
          foreach ($web_page_archive->getUrlList() as $url) {
            $urlObj = Url::fromUri($url);
            $urlObj->setOptions(['attributes' => ['target' => '_blank']]);
            $list[] = Link::fromTextAndUrl($url, $urlObj)->toString();
          }
          $form['help'] = [
            '#theme' => 'item_list',
            '#prefix' => $this->t('Click "Start Run" to capture these @types:', ['@type' => $web_page_archive->getUrlType()]),
            '#items' => $list,
          ];
          break;

        default:
          $form['help'] = [
            '#markup' => $this->t('Click "Start Run" to initiate capture.'),
          ];
      }
    }
    // If there are missing dependencies, display message.
    else {
      $this->messenger()->addError($this->t("Capture job can't run at this time!"));
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
