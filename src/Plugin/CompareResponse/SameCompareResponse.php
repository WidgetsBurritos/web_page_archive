<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

/**
 * The response that indicates the variance threshold for a response.
 */
class SameCompareResponse extends VarianceCompareResponse {

  /**
   * Constructs a new SameCompareResponse object.
   *
   * Two things that are the same have zero variance, so extending the
   * VarianceCompareResponse and setting the variance value to zero makes sense.
   */
  public function __construct() {
    parent::__construct('0');
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_same_compare_response';
  }

  /**
   * Renders this response.
   */
  public function renderable(array $options = []) {
    return ['#markup' => $this->t('Captures are identical.')];
  }

}
