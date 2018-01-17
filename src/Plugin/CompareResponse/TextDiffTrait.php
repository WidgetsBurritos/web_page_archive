<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

/**
 * Trait for compare responses that use text-based diffing.
 */
trait TextDiffTrait {

  /**
   * Array of differences.
   *
   * @var array
   */
  protected $diff = [];

  /**
   * Sets the Diff object containing the comparison result (if applicable).
   */
  public function setDiff($diff) {
    $this->diff = $diff;
  }

  /**
   * Retrieves the diff values.
   */
  public function getDiff() {
    return isset($this->diff) ? $this->diff : [];
  }

}
