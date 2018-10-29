<?php

namespace Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility;

use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\ComparisonUtility\FileSizeComparisonUtility;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\SliderScreenshotCompareResponse;

/**
 * Provides slider for diff.
 *
 * @ComparisonUtility(
 *   id = "wpa_screenshot_capture_slider_compare",
 *   label = @Translation("Screenshot: Slider", context = "Web Page Archive"),
 *   description = @Translation("Compares images via slider.", context = "Web Page Archive"),
 *   tags = {"screenshot"}
 * )
 */
class SliderComparisonUtility extends FileSizeComparisonUtility {

  /**
   * {@inheritdoc}
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $data = []) {
    return new SliderScreenshotCompareResponse(0);
  }

}
