<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

use Drupal\web_page_archive\Plugin\CaptureResponseBase;

/**
 * Screenshot capture response.
 */
class ScreenshotCaptureResponse extends CaptureResponseBase {

  /**
   * ScreenshotCaptureResponse constructor.
   *
   * @param string $content
   *   The response contents.
   */
  public function __construct($content) {
    $this->setType('uri')->setContent($content);
  }

}
