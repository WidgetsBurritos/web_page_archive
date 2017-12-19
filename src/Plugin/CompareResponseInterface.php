<?php

namespace Drupal\web_page_archive\Plugin;

/**
 * Defines an interface for Compare responses.
 */
interface CompareResponseInterface {

  /**
   * Retrieve content.
   *
   * @return string
   *   Stringified content.
   */
  public function getContent();

  /**
   * Set response content.
   *
   * @param string $content
   *   The response content.
   *
   * @return \Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   Reference to self.
   */
  public function setContent($content);

  /**
   * Retrieves the variance to 1 significant digit.
   *
   * @return float
   *   A percentage value indicating how much two capture responses vary.
   */
  public function getVariance();

}
