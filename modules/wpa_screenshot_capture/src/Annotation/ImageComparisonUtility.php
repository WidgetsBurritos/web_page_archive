<?php

namespace Drupal\wpa_screenshot_capture\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Capture utility item annotation object.
 *
 * @see \Drupal\wpa_screenshot_capture\Plugin\ImageComparisonUtilityManager
 * @see plugin_api
 *
 * @Annotation
 */
class ImageComparisonUtility extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the capture utility.
   *
   * This will be shown when adding or configuring this capture utility.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

}
