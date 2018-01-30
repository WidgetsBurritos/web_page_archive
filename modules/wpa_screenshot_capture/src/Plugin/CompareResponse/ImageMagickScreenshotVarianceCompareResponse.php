<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CompareResponse;

/**
 * Screenshot variance compare response based on ImageMagick.
 */
class ImageMagickScreenshotVarianceCompareResponse extends ScreenshotVarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_imagemagick_screenshot_variance_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function getHumanReadableName() {
    return $this->t('Pixel');
  }

  /**
   * {@inheritdoc}
   */
  public function renderPreview(array $options = []) {
    // TODO: Implement this later.
    return ['#markup' => $this->t('[TODO: ImageMagick]')];
  }

  /**
   * {@inheritdoc}
   */
  public function renderFull(array $options = []) {
    // TODO: Implement this later.
    return ['#markup' => $this->t('[TODO: ImageMagick]')];
  }

}
