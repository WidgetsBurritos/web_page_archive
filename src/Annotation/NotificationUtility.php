<?php

namespace Drupal\web_page_archive\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Notification utility item annotation object.
 *
 * @see \Drupal\web_page_archive\Plugin\NotificationUtilityManager
 * @see plugin_api
 *
 * @Annotation
 */
class NotificationUtility extends Plugin {

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
   * A brief description of the Notification utility.
   *
   * This will be shown when adding or configuring this Notification utility.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

}
