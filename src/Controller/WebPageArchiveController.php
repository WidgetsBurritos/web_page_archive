<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueFactory;
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
   * Constructs a new WebPageArchiveController object.
   */
  public function __construct(QueueFactory $queue) {
    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('queue')
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

}
