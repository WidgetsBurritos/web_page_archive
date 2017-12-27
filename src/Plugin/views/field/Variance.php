<?php

namespace Drupal\web_page_archive\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to show data of serialized fields.
 *
 * @ViewsField("web_page_archive_variance")
 */
class Variance extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = (float) $this->getValue($values);

    // If negative, return an empty string.
    if ($value < 0) {
      return '';
    }

    // If non-negative, display variance as a percentage.
    return $this->t('Variance: @variance%', ['@variance' => $value]);
  }

}
