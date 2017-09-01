<?php

namespace Drupal\web_page_archive\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;

/**
 * Defines the 'integer' field type.
 *
 * @FieldType(
 *   id = "wpa_default_integer",
 *   label = @Translation("Number (integer) with defaults enabled."),
 *   description = @Translation("This field stores a number in the database as an integer."),
 *   category = @Translation("Number"),
 *   default_widget = "number",
 *   default_formatter = "number_integer"
 * )
 */
class DefaultIntegerItem extends IntegerItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    if (isset($field_definition->getDefaultValueLiteral()[0]['value'])) {
      $schema['columns']['value']['default'] = $field_definition->getDefaultValueLiteral()[0]['value'];
    }
    else {
      $schema['columns']['value']['default'] = 0;
    }
    return $schema;
  }

}
