<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

use Drupal\web_page_archive\Plugin\CaptureResponseBase;

/**
 * URI capture response.
 */
class UriCaptureResponse extends CaptureResponseBase {

  /**
   * UriCaptureResponse constructor.
   *
   * @param string $content
   *   The response contents.
   */
  public function __construct($content, $capture_url) {
    $this->setType('uri')
      ->setContent($content)
      ->setCaptureUrl($capture_url);
  }

  /**
   * {@inheritdoc}
   */
  public function getCaptureSize() {
    // TODO: What to do if remote URL instead of local file path?
    if (!is_readable($this->getContent())) {
      throw new \Exception("Can't read file.");
    }
    return filesize($this->getContent());
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return $this->content;
  }
}
