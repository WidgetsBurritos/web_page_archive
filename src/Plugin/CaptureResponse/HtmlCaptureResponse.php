<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

use Drupal\web_page_archive\Plugin\CaptureResponseBase;

/**
 * Html capture response.
 */
class HtmlCaptureResponse extends CaptureResponseBase {

  /**
   * HtmlCaptureResponse constructor.
   *
   * @param string $content
   *   The response contents.
   */
  public function __construct($content) {
    $this->setType('html')->setContent($content);
  }

}
