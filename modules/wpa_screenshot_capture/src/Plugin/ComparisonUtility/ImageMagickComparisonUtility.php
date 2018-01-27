<?php

namespace Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility;

use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\ComparisonUtilityBase;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\ImageMagickScreenshotVarianceCompareResponse;

/**
 * Captures screenshot of a remote uri.
 *
 * @ComparisonUtility(
 *   id = "wpa_screenshot_capture_imagemagick_compare",
 *   label = @Translation("Screenshot: ImageMagick", context = "Web Page Archive"),
 *   description = @Translation("Compares images and generates diff images.", context = "Web Page Archive"),
 *   tags = {"screenshot"}
 * )
 */
class ImageMagickComparisonUtility extends ComparisonUtilityBase {

  /**
   * {@inheritdoc}
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b) {
    // TODO: Build this in a future version.
    return new ImageMagickScreenshotVarianceCompareResponse(0);
  }

}
