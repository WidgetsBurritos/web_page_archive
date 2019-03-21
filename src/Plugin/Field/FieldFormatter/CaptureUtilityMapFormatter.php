<?php

namespace Drupal\web_page_archive\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'web_page_archive_capture_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "web_page_archive_capture_utility_map_formatter",
 *   label = @Translation("Web page archive capture utility map formatter"),
 *   field_types = {
 *     "map"
 *   }
 * )
 */
class CaptureUtilityMapFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $values = array_values($item->toArray());
    foreach ($values as $idx => $value) {
      // If incomplete, serialize and then unserialize to clean this up.
      if (get_class($value) == '__PHP_Incomplete_Class') {
        $values[$idx] = unserialize(serialize($value));
      }
    }
    return nl2br(Html::escape(implode("\n", $values)));
  }

}
