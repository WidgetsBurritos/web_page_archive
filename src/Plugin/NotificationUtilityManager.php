<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides the Notification utility plugin manager.
 */
class NotificationUtilityManager extends DefaultPluginManager {

  /**
   * Constructs a new NotificationUtilityManager object.
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
    parent::__construct('Plugin/NotificationUtility', $namespaces, $module_handler, 'Drupal\web_page_archive\Plugin\NotificationUtilityInterface', 'Drupal\web_page_archive\Annotation\NotificationUtility');

    $this->alterInfo('web_page_archive_notification_utility_info');
    $this->setCacheBackend($cache_backend, 'web_page_archive_notification_utility_plugins');
  }

}
