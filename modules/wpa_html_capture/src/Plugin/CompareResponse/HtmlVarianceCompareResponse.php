<?php

namespace Drupal\wpa_html_capture\Plugin\CompareResponse;

use Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse;

/**
 * The response that indicates the variance threshold for a HTML response.
 */
class HtmlVarianceCompareResponse extends VarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_html_variance_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function getHumanReadableName() {
    return $this->t('Line');
  }

}
