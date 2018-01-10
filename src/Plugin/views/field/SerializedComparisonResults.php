<?php

namespace Drupal\web_page_archive\Plugin\views\field;

use Drupal\views\Plugin\views\field\Serialized;
use Drupal\views\ResultRow;

/**
 * Field handler to show data of serialized fields.
 *
 * @ViewsField("web_page_archive_serialized_comparison_results")
 */
class SerializedComparisonResults extends Serialized {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $values->{$this->field_alias};

    if ($this->options['format'] == 'unserialized') {
      $options = unserialize($value);
      if (isset($options['compare_response'])) {
        return $options['compare_response']->renderable($options);
      }
    }
    return $this->t('Could not render results');
  }

}
