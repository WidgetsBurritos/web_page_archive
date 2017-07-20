<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Capture utility plugins.
 */
abstract class CaptureUtilityBase extends PluginBase implements CaptureUtilityInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function missingDependencies() {
    return [];
  }

}
