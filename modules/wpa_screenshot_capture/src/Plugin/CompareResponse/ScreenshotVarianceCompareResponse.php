<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CompareResponse;

use Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse;

/**
 * The response that indicates the variance threshold for a screenshot response.
 */
class ScreenshotVarianceCompareResponse extends VarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_screenshot_variance_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function renderable(array $options = []) {
    try {
      // Ensure run comparison is valid.
      if (!isset($options['run_comparison'])) {
        throw new \Exception('ScreenshotVarianceCompareResponse: Missing comparison entity.');
      }

      // Ensure index is valid.
      $results = $options['run_comparison']->getResults();
      if (!isset($options['index']) || !isset($results[$options['index']])) {
        throw new \Exception('ScreenshotVarianceCompareResponse: Invalid index.');
      }
      $options['results'] = $results;

      // Build render array.
      return (isset($options['mode']) && $options['mode'] == 'full') ?
        $this->renderFull($options) : $this->renderPreview($options);
    }
    catch (Exception $e) {
      watchdog_exception($e);
      return ['#markup' => $this->t('Invalid comparison.')];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function renderFull(array $options = []) {
    // Ensure both screenshots exist.
    $result = $options['results'][$options['index']];
    $run1 = $options['run_comparison']->getRun1();
    $run2 = $options['run_comparison']->getRun2();
    if (!$result['has_left'] || !$result['has_right'] || !isset($run1) || !isset($run2)) {
      throw new \Exception('ScreenshotVarianceCompareResponse: Comparison requires two screenshots.');
    }

    // Ensure deltas exist.
    $run1_captured = $run1->getCapturedArray();
    $run2_captured = $run2->getCapturedArray();

    // Ensure capture responses are loaded.
    $run1_details = unserialize($run1_captured[$result['delta1']]->getString());
    $run2_details = unserialize($run2_captured[$result['delta2']]->getString());
    if (!isset($run1_details['capture_response'])) {
      throw new \Exception('ScreenshotVarianceCompareResponse: Run 1 capture response missing.');
    }
    if (!isset($run2_details['capture_response'])) {
      throw new \Exception('ScreenshotVarianceCompareResponse: Run 2 capture response missing.');
    }

    // Build the render array.
    $build = [
      '#theme' => 'wpa_screenshot_compare',
      '#left' => $run1_details['capture_response']->renderable(['mode' => 'full']),
      '#right' => $run2_details['capture_response']->renderable(['mode' => 'full']),
    ];
    return $build;
  }

}
