<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CompareResponse;

/**
 * Screenshot variance compare response based on file size.
 */
class FileSizeScreenshotVarianceCompareResponse extends ScreenshotVarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_file_size_screenshot_variance_compare_response';
  }

}
