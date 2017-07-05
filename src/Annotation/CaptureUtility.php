<?php

namespace Drupal\web_page_archive\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Capture utility item annotation object.
 *
 * @see \Drupal\web_page_archive\Plugin\CaptureUtilityManager
 * @see plugin_api
 *
 * @Annotation
 */
class CaptureUtility extends Plugin {


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

}
