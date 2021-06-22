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
   * Renders a serialized string.
   *
   * @param string $value
   *   Serialized string.
   *
   * @return array|null
   *   Returns render array or NULL if not applicable.
   */
  public function doRenderSerialized($value) {
    $options = unserialize($value);
    if (isset($options['capture_response'])) {
      if (get_class($options['capture_response']) === '__PHP_Incomplete_Class') {
        $response = new \ArrayObject($options['capture_response']);
        return ['#markup' => $this->t('Invalid Capture Response Object: @class', ['@class' => $response['__PHP_Incomplete_Class_Name']])];
      }
      return $options['capture_response']->renderable($options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $values->{$this->field_alias};

    if ($this->options['format'] == 'unserialized') {
      if ($ret = $this->doRenderSerialized($value)) {
        return $ret;
      }
    }

    return parent::render($values);
  }

}
