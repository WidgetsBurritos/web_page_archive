<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\Component\Plugin\PluginBase;

/**
 * Captures screenshot of a remote uri.
 *
 * @CaptureUtility(
 *   id = "ScreenshotCapture",
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
    // TODO: Do something.
    $this->response = NULL;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

}
