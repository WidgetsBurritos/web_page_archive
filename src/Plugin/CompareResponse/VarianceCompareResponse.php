<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

use Drupal\web_page_archive\Plugin\CompareResponseBase;

/**
 * The response that indicates the variance threshold for a response.
 */
class VarianceCompareResponse extends CompareResponseBase {

  /**
   * Creates a new VarianceCompareResponse object.
   */
  public function __construct($variance) {
    $this->variance = (float) $variance;
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_variance_compare_response';
  }

  /**
   * Renders this response.
   */
  public function renderable(array $options = []) {
    return ['#markup' => $this->t('There is a @variance% difference between the two captures.', ['@variance' => $this->variance])];
  }

}
