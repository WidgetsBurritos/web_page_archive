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
   * @param string $id
   *   The response content.
   *
   * @return \Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   Reference to self.
   */
  protected function setId($id) {
    $this->id = $id;

    return $this;
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
   * Retrieves an id for the capture response type.
   */
  public static function getId() {
    // We should warn if this method is not overridden. This will allow for
    // graceful handling of any existing capture responses. In next major
    // release, this should get converted to an abstract method.
    $class = get_called_class();
    \Drupal::logger('web_page_archive')
      ->notice('@class should override the getId() method.', ['@class' => $class]);
    return 'wpa_capture_response';
  }

  /**
   * Performs a comparison two responses.
   */
  public static function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $compare_utilities, array $tags = [], array $data = []) {
    $comparison_utility_manager = \Drupal::service('plugin.manager.comparison_utility');
    $response_factory = \Drupal::service('web_page_archive.compare.response');
    $response_collection = $response_factory->getCompareResponseCollection();
    foreach ($compare_utilities as $compare_utility) {
      if ($compare_utility) {
        $instance = $comparison_utility_manager->createInstance($compare_utility);
        foreach ($tags as $tag) {
          if ($instance->isApplicable($tag)) {
            $comparison_response = $instance->compare($a, $b, $data);
            $response_collection->addResponse($comparison_response);
          }
        }
      }
    }
    return $response_collection;
  }

  /**
   * Renders this response.
   */
  abstract public function renderable(array $options = []);

}
