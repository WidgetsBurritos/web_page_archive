<?php

namespace Drupal\web_page_archive\Event;

use Drupal\web_page_archive\Plugin\NotificationUtilityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user is to be notified.
 */
class NotificationEvent extends Event {

  const EVENT_NAME = 'wpa_notify';

  /**
   * Notification utility.
   *
   * @var Drupal\web_page_archive\Plugin\NotificationUtilityInterface
   */
  protected $notificationUtility;

  /**
   * Configuration array.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Replacements array.
   *
   * @var array
   */
  protected $replacements;

  /**
   * Creates new NotificationEvent.
   *
   * @param \Drupal\web_page_archive\Plugin\NotificationUtilityInterface $notification_utility
   *   Notification utility.
   * @param array $configuration
   *   Configuration array.
   * @param array $replacements
   *   Replacements array.
   */
  public function __construct(NotificationUtilityInterface $notification_utility, array $configuration = [], array $replacements = []) {
    $this->notificationUtility = $notification_utility;
    $this->configuration = $configuration;
    $this->replacements = $replacements;
  }

  /**
   * Retrieves event configuration settings.
   *
   * @return array
   *   Settings array.
   */
  public function getEventConfiguration() {
    return $this->configuration;
  }

  /**
   * Retrieves the notification utility.
   */
  public function getNotificationUtility() {
    return $this->notificationUtility;
  }

  /**
   * Retrieves list of replacements.
   *
   * @return array
   *   List of replacements.
   */
  public function getReplacements() {
    return $this->replacements;
  }

}
