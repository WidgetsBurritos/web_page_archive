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

  /**
   * Calulates variance based on a edit array from DiffEngine.
   */
  public static function calculateDiffVariance(array $diff_edits) {
    // If both strings are empty, there is 0% variance.
    $counts = [
      'empty' => 0,
      'add' => 1,
      'copy' => 0,
      'change' => 1,
      'delete' => 1,
      'copy-and-change' => 1,
      'copy-change-copy' => 1,
      'copy-change-copy-add' => 1,
      'copy-delete' => 1,
    ];
    $changes = 0;
    $total_ct = 0;
    foreach ($diff_edits as $diff_edit) {
      if (isset($counts[$diff_edit->type])) {
        $lines = max(count((array) $diff_edit->orig), count((array) $diff_edit->closing));
        $changes += $counts[$diff_edit->type] * $lines;
        $total_ct += $lines;
      }
    }
    return $total_ct > 0 ? 100 * $changes / $total_ct : 0;
  }

}
