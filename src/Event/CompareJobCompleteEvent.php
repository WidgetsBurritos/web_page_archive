<?php

namespace Drupal\web_page_archive\Event;

use Drupal\web_page_archive\Entity\RunComparisonInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a compare job is finished.
 */
class CompareJobCompleteEvent extends Event {

  const EVENT_NAME = 'wpa_compare_complete';

  /**
   * The completed comparison entity.
   *
   * @var \Drupal\web_page_archive\Entity\RunComparisonInterface
   */
  public $comparisonEntity;

  /**
   * Constructs the object.
   *
   * @param \Drupal\web_page_archive\Entity\RunComparisonInterface $comparison_entity
   *   The comparison entity that just completed.
   */
  public function __construct(RunComparisonInterface $comparison_entity) {
    $this->comparisonEntity = $comparison_entity;
  }

}
