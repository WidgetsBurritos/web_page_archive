<?php

namespace Drupal\web_page_archive\Event;

use Drupal\web_page_archive\Entity\WebPageArchiveRunInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a capture job is finished.
 */
class CaptureJobCompleteEvent extends Event {

  const EVENT_NAME = 'wpa_capture_complete';

  /**
   * The completed run entity.
   *
   * @var \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface
   */
  public $runEntity;

  /**
   * Constructs the object.
   *
   * @param \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface $run_entity
   *   The run entity that just completed.
   */
  public function __construct(WebPageArchiveRunInterface $run_entity) {
    $this->runEntity = $run_entity;
  }

}
