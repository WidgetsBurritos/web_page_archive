<?php

namespace Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility;

use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\ComparisonUtilityBase;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\FileSizeScreenshotVarianceCompareResponse;

/**
 * Captures screenshot of a remote uri.
 *
 * @ComparisonUtility(
 *   id = "wpa_screenshot_capture_file_size_compare",
 *   label = @Translation("Screenshot: File Size", context = "Web Page Archive"),
 *   description = @Translation("Compares images based on file size.", context = "Web Page Archive"),
 *   tags = {"screenshot"}
 * )
 */
class FileSizeComparisonUtility extends ComparisonUtilityBase {

  /**
   * {@inheritdoc}
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b) {
    $size1 = $a->getCaptureSize();
    $size2 = $b->getCaptureSize();
    $variance = 100 * abs($size2 - $size1) / $size1;

    if ($variance === 0) {
      return $this->compareResponseFactory->getSameCompareResponse();
    }

    $response = new FileSizeScreenshotVarianceCompareResponse($variance);
    $response->setFile1Size($size1);
    $response->setFile2Size($size2);
    return $response;
  }

}
