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

}
