<?php

namespace Drupal\wpa_screenshot_capture\Plugin;

/**
 * Defines an interface for image comparison responses.
 */
interface ImageComparisonUtilityInterface {

  /**
   * Returns render array of the configuration of the image comparison utility.
   *
   * @return array
   *   A render array.
   */
  public function getSummary();

  /**
   * Returns the image comparison utility label.
   *
   * @return string
   *   The image comparison utility label.
   */
  public function label();

  /**
   * Returns the unique ID representing the image comparison utility.
   *
   * @return string
   *   The image comparison utility ID.
   */
  public function getUuid();

  /**
   * Returns the weight of the image comparison utility.
   *
   * @return int|string
   *   Either integer weight of image comparison utility, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this image comparison utility.
   *
   * @param int $weight
   *   The weight for this image comparison utility.
   *
   * @return $this
   */
  public function setWeight($weight);

}
