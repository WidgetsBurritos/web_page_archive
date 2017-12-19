<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for capture responses.
 */
abstract class CompareResponseBase implements CompareResponseInterface {

  use StringTranslationTrait;

  /**
   * The response content.
   *
   * @var string
   */
  protected $content = '';

  /**
   * The variance value.
   *
   * @var string
   */
  protected $variance = -1;

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * {@inheritdoc}
   */
  public function setContent($content) {
    $this->content = $content;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariance() {
    return round($this->variance, 1);
  }

  /**
   * Retrieves the ID of the compare response.
   */
  abstract public static function getId();

  /**
   * Renders this response.
   */
  abstract public function renderable(array $options = []);

}
