<?php

namespace Drupal\web_page_archive\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\web_page_archive\Event\CaptureJobCompleteEvent;
use Drupal\web_page_archive\Event\CompareJobCompleteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the web page archive events.
 */
class WebPageArchiveEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      CaptureJobCompleteEvent::EVENT_NAME => 'captureComplete',
      CompareJobCompleteEvent::EVENT_NAME => 'compareComplete',
    ];
  }

  /**
   * React to a capture job completing.
   *
   * @param \Drupal\web_page_archive\Event\CaptureJobCompleteEvent $event
   *   Capture job completion event.
   */
  public function captureComplete(CaptureJobCompleteEvent $event) {
    \Drupal::messenger()->addStatus($this->t('The capture has been completed.'));
  }

  /**
   * React to a compare job completing.
   *
   * @param \Drupal\web_page_archive\Event\CompareJobCompleteEvent $event
   *   Compare job completion event.
   */
  public function compareComplete(CompareJobCompleteEvent $event) {
    \Drupal::messenger()->addStatus($this->t('The comparison has been completed.'));
  }

}
