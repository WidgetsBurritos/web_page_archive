<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

use Drupal\web_page_archive\Plugin\CompareResponseBase;

/**
 * The response when no comparison could be performed.
 *
 * This should only ever get used by the CaptureResponseBase as this is meant
 * to indicate that a capture response has failed to implement compare().
 */
class EmptyCompareResponse extends CompareResponseBase {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_empty_compare_response';
  }

  /**
   * Renders this response.
   */
  public function renderable(array $options = []) {
    return ['#markup' => $this->t('No comparison could be performed.')];
  }

}
