<?php

namespace Drupal\web_page_archive\Plugin\views\field;

use Drupal\views\Plugin\views\field\Serialized;
use Drupal\views\ResultRow;

/**
 * Field handler to show data of serialized fields.
 *
 * @ViewsField("web_page_archive_serialized_capture")
 */
class SerializedCapture extends Serialized {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $values->{$this->field_alias};

    if ($this->options['format'] == 'unserialized') {
      $options = unserialize($value);
      if (isset($options['capture_response'])) {
        return $options['capture_response']->renderable($options);
      }
    }

    return parent::render($values);
  }

}
