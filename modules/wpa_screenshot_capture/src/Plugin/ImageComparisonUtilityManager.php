<?php

namespace Drupal\wpa_screenshot_capture\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the image comparison utility plugin manager.
 */
class ImageComparisonUtilityManager extends DefaultPluginManager {

  /**
   * Constructs a new ImageComparisonUtilityManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ImageComparisonUtility', $namespaces, $module_handler, 'Drupal\wpa_screenshot_capture\Plugin\ImageComparisonUtilityInterface', 'Drupal\wpa_screenshot_capture\Annotation\ImageComparisonUtility');

    $this->alterInfo('wpa_image_comparison_utility_info');
    $this->setCacheBackend($cache_backend, 'wpa_image_comparison_utility_plugins');
  }

}
