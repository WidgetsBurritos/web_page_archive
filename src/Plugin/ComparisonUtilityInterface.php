<?php

namespace Drupal\web_page_archive\Plugin;

/**
 * Defines an interface for image comparison responses.
 */
interface ComparisonUtilityInterface {

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

  /**
   * Indicates whether or not the compare utility is available for use.
   *
   * @return bool
   *   A boolean value indicating available. Defaults to TRUE.
   */
  public function isAvailable();

  /**
   * Indicates whether or not a tag is applicable for this comparison utility.
   *
   * @return bool
   *   A boolean value indicating if the utility is applicable for the tag.
   */
  public function isApplicable($tag);

  /**
   * Indicates whether or not a comparison utility is filterable.
   */
  public function isFilterable();

  /**
   * Performs a comparison between two capture responses.
   *
   * @return \Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   The results of a comparison.
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $data);

  /**
   * Retrieves a filename based on the specified data.
   *
   * @param array $data
   *   Capture data array.
   * @param string $extension
   *   File extension of the comparison.
   *
   * @return string
   *   Retrieves a filename for a capture.
   */
  public function getFileName(array $data, $extension);

}
