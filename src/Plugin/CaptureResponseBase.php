<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for capture responses.
 */
abstract class CaptureResponseBase implements CaptureResponseInterface {

  use StringTranslationTrait;

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
   * The response type.
   *
   * @var uri
   */
  protected $captureUrl = '';

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
   * {@inheritdoc}
   */
  public function getCaptureUrl() {
    return $this->captureUrl;
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
   * Set capture URL.
   *
   * @param string $url
   *   Indicates the URL that is getting captured.
   *
   * @return \Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   Reference to self.
   */
  protected function setCaptureUrl($url) {
    $this->captureUrl = $url;

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

  /**
   * {@inheritdoc}
   */
  public function getCaptureSize() {
    return 0;
  }

  /**
   * Renders this response.
   */
  abstract public function renderable(array $options = []);

}
