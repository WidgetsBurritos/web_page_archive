<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\web_page_archive\Event\NotificationEvent;

/**
 * Defines an interface for Notification utility plugins.
 */
interface NotificationUtilityInterface extends PluginInspectionInterface, ConfigurableInterface, DependentPluginInterface {

  /**
   * Render array containing form fields to add for this notification utility.
   *
   * @return array
   *   Render array.
   */
  public static function getFormFields($variables);

  /**
   * Triggers a notification event based on specified configuration.
   *
   * @param array $configuration
   *   Configuration settings.
   * @param array $replacements
   *   Array of replacement strings.
   */
  public function triggerEvent(array $configuration, array $replacements = []);

  /**
   * Attempts to handle the specified event.
   *
   * @param \Drupal\web_page_archive\Event\NotificationEvent $event
   *   Notification event to handle.
   */
  public function handleEvent(NotificationEvent $event);

  /**
   * Retrieves a list of required configuration keys.
   *
   * @return array
   *   List of configuration required keys for using this notification utility.
   */
  public function getRequiredConfigurationKeys();

  /**
   * Verifies required configuration keys are in configuration array.
   *
   * @param array $configuration
   *   Configuration to verify.
   *
   * @throws \Exception
   */
  public function checkRequiredConfigurationKeys(array $configuration);

}
