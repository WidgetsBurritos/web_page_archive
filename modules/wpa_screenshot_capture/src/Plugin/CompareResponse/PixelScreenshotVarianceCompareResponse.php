<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CompareResponse;

/**
 * Screenshot variance compare response based on pixels.
 */
class PixelScreenshotVarianceCompareResponse extends ScreenshotVarianceCompareResponse {

  /**
   * Creates a new PixelScreenshotVarianceCompareResponse object.
   */
  public function __construct($variance, $compare_path) {
    parent::__construct($variance);
    $this->pixelComparePath = $compare_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_pixel_screenshot_variance_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function renderPreview(array $options = []) {
    if (empty($this->pixelComparePath) || !file_exists($this->pixelComparePath)) {
      $render['pixel'] = [
        '#prefix' => '<div class="wpa-comparison-pixel">',
        '#markup' => $this->t('Could not display pixel comparision. Please check your ImageMagick settings.'),
        '#suffix' => '</div>',
      ];
    }
    else {
      $render['pixel'] = [
        '#theme' => 'image_style',
        '#style_name' => 'web_page_archive_thumbnail',
        '#uri' => $this->pixelComparePath,
        '#attached' => ['library' => ['web_page_archive/admin']],
        '#prefix' => '<div class="wpa-comparison-pixel">',
        '#suffix' => '</div>',
      ];
      $render['variance'] = [
        '#prefix' => '<div class="wpa-comparison-variance">',
        '#markup' => $this->t('Pixel Variance: @variance%', ['@variance' => $this->getVariance()]),
        '#suffix' => '</div>',
      ];
    }
    return $render;
  }

}
