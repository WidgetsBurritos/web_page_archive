<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtilityBase;

/**
 * Captures screenshot of a remote uri.
 *
 * @CaptureUtility(
 *   id = "ScreenshotCaptureUtility",
 *   label = @Translation("Screenshot capture utility", context = "Web Page Archive"),
 * )
 */
class ScreenshotCaptureUtility extends CaptureUtilityBase {

  /**
   * Most recent response.
   *
   * @var string|null
   */
  private $response = NULL;

  /**
   * {@inheritdoc}
   */
  public function captureUrl($uri) {
    // TODO: Do the actual capture.
    $this->response = new ScreenshotCaptureResponse('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg');

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

}
