<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the image comparison utility plugin manager.
 */
class ComparisonUtilityManager extends DefaultPluginManager {

  /**
   * Constructs a new ComparisonUtilityManager object.
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
    parent::__construct('Plugin/ComparisonUtility', $namespaces, $module_handler, 'Drupal\web_page_archive\Plugin\ComparisonUtilityInterface', 'Drupal\web_page_archive\Annotation\ComparisonUtility');

    $this->alterInfo('web_page_archive_comparison_utility_info');
    $this->setCacheBackend($cache_backend, 'web_page_archive_comparison_utility_plugins');
  }

}
