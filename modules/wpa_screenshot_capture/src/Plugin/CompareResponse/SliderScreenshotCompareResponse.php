<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CompareResponse;

use Drupal\Component\Serialization\Json;

/**
 * Screenshot compare response with slider.
 */
class SliderScreenshotCompareResponse extends ScreenshotVarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $variance = 0;
    parent::__construct($variance);
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_slider_screenshot_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function renderPreview(array $options = []) {
    $render['link'] = [
      '#type' => 'link',
      '#url' => $this->getFullModeUrlFromOptions($options),
      '#title' => $this->t('Compare Images'),
      '#attributes' => [
        'class' => ['use-ajax', 'button', 'button--small', 'button--primary'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 1280]),
      ],
    ];

    return $this->attachLibrary($render);
  }

  /**
   * {@inheritdoc}
   */
  public function renderFull(array $options = []) {
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
