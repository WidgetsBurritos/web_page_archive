<?php

namespace Drupal\web_page_archive\Plugin;

/**
 * Base class for capture responses.
 */
abstract class CaptureResponseBase implements CaptureResponseInterface {

  /**
   * Response content is a URI.
   */
  const TYPE_URI = 'uri';

  /**
   * Response content is HTML.
   */
  const TYPE_HTML = 'html';

  /**
   * The response content.
   *
   * @var string
   */
  protected $content = '';

  /**
   * The response type.
   *
   * @var string
   */
  protected $type = '';

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set response content.
   *
   * @param string $content
   *   The response content.
   *
   * @return \Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   Reference to self.
   */
  protected function setContent($content) {
    $this->content = $content;

    return $this;
  }

  /**
   * Set response type.
   *
   * @param string $type
   *   Indicates response type (e.g. 'html' or 'uri').
   *
   * @return \Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   Reference to self.
   */
  protected function setType($type) {
    $this->type = $type;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSerialized() {
    return serialize([
      'type' => $this->type,
      'content' => $this->content,
    ]);
  }

}
