<?php

namespace Drupal\wpa_screenshot_capture\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class ScreenshotCatureController.
 *
 *  Returns responses for screenshot capture response routes.
 *
 * @package Drupal\web_page_archive\Controller
 */
class ScreenshotCaptureController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Helper function to retrieve a revision delta.
   */
  private function getRevisionDelta($web_page_archive_run_revision, $delta) {
    if (empty($web_page_archive_run_revision->field_captures->offsetGet($delta)->value)) {
      return NULL;
    }
    $response = unserialize($web_page_archive_run_revision->field_captures->offsetGet($delta)->value);
    if (empty($response['capture_response'])) {
      return NULL;
    }

    return $response;
  }

  /**
   * Render array for modal.
   */
  public function screenshotModal($web_page_archive_run_revision, $delta) {
    $response = $this->getRevisionDelta($web_page_archive_run_revision, $delta);
    if (!isset($response)) {
      return ['#markup' => $this->t('Invalid capture response.')];
    }
    return $response['capture_response']->renderable(['mode' => 'full']);
  }

  /**
   * Capture delta.
   */
  public function screenshotModalTitle($web_page_archive_run_revision, $delta) {
    $response = $this->getRevisionDelta($web_page_archive_run_revision, $delta);
    if (!isset($response)) {
      return $this->t('Error!');
    }
    return $response['capture_url'];
  }

}
