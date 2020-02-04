<?php

namespace Drupal\wpa_skeleton_capture\EventSubscriber;

use Drupal\web_page_archive\Event\CaptureJobCompleteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the web page archive events.
 */
class WebPageArchiveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      CaptureJobCompleteEvent::EVENT_NAME => 'captureComplete',
    ];
  }

  /**
   * React to a capture job completing.
   *
   * @param \Drupal\web_page_archive\Event\CaptureJobCompleteEvent $event
   *   Capture job completion event.
   */
  public function captureComplete(CaptureJobCompleteEvent $event) {
    $wpa_run = $event->getRunEntity();
    foreach ($wpa_run->getConfigEntity()->getCaptureUtilities() as $id => $utility) {
      if ($utility->getPluginId() == 'wpa_skeleton_capture') {
        $replacements = [];
        $utility->notify('capture_complete_all', $replacements);
      }
    }
  }

}
