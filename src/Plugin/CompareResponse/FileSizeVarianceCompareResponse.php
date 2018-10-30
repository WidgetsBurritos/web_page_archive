<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

/**
 * Cariance compare response based on file size.
 */
class FileSizeVarianceCompareResponse extends VarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_file_size_variance_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function renderPreview(array $options = []) {
    $render = [];
    // Show file sizes.
    $ct = 0;
    foreach ($options['runs'] as $details) {
      $ct++;
      $replacements = [
        '@number' => $ct,
        '@size' => format_size($details[$options["delta{$ct}"]]['capture_size']),
      ];
      $render["size{$ct}"] = [
        '#prefix' => '<div class="wpa-comparison-file-size">',
        '#markup' => $this->t('Size @number: @size', $replacements),
        '#suffix' => '</div>',
      ];
    }
    $render['variance'] = [
      '#prefix' => '<div class="wpa-comparison-variance">',
      '#markup' => $this->t('Size Variance: @variance%', ['@variance' => $this->getVariance()]),
      '#suffix' => '</div>',
    ];

    return $render;
  }

}
