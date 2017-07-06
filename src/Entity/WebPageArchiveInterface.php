<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\web_page_archive\Plugin\CaptureUtilityInterface;

/**
 * Provides an interface for defining Web page archive entity entities.
 */
interface WebPageArchiveInterface extends ConfigEntityInterface {

  /**
   * Returns a specific capture utility.
   *
   * @param string $capture_utility
   *   The capture utility ID.
   *
   * @return \Drupal\web_page_archive\Plugin\CaptureUtilityInterface
   *   The capture utility object.
   */
  public function getCaptureUtility($capture_utility);

  /**
   * Returns the capture utilities for this archive.
   *
   * @return \Drupal\Core\Plugin\DefaultLazyPluginCollection|\Drupal\web_page_archive\Plugin\CaptureUtilityInterface[]
   *   The capture utility plugin collection.
   */
  public function getCaptureUtilities();

  /**
   * Saves a capture utility for this archive.
   *
   * @param array $configuration
   *   An array of capture utility configuration.
   *
   * @return string
   *   The capture utility ID.
   */
  public function addCaptureUtility(array $configuration);

  /**
   * Deletes a capture utility from this archive.
   *
   * @param \Drupal\web_page_archive\Plugin\CaptureUtilityInterface $capture_utility
   *   The capture utility object.
   *
   * @return $this
   */
  public function deleteCaptureUtility(CaptureUtilityInterface $capture_utility);

}
