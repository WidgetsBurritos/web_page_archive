<?php

namespace Drupal\web_page_archive\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Formatter the capture size in a human readable way.
 *
 * @FieldFormatter(
 *   id = "wpa_capture_size",
 *   label = @Translation("Capture size"),
 *   field_types = {
 *     "integer",
 *     "float"
 *   }
 * )
 */
class CaptureSizeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => format_size($item->value)];
    }

    return $elements;
  }

}
