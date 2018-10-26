<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for capture utility plugins.
 */
interface CaptureUtilityInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Captures the specified URL.
   *
   * @param array $data
   *   Array containing capture info.
   *
   * @return CaptureUtilityInterface
   *   Returns reference to self.
   */
  public function capture(array $data);

  /**
   * Retrieves response from most recent capture.
   *
   * @return Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   A capture response object.
   */
  public function getResponse();

  /**
   * Determines whether or not dependencies are missing.
   *
   * @return array
   *   Array containing missing dependencies.
   */
  public function missingDependencies();

  /**
   * Returns a render array of the configuration of the capture utility.
   *
   * @return array
   *   A render array.
   */
  public function getSummary();

  /**
   * Returns the capture utility label.
   *
   * @return string
   *   The capture utility label.
   */
  public function label();

  /**
   * Returns the unique ID representing the capture utility.
   *
   * @return string
   *   The capture utility ID.
   */
  public function getUuid();

  /**
   * Returns the weight of the image effect.
   *
   * @return int|string
   *   Either the integer weight of the image effect, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this image effect.
   *
   * @param int $weight
   *   The weight for this image effect.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Performs cleanup on a config entity for the capture utility.
   *
   * @param string $entity_id
   *   Specific entity to cleanup.
   */
  public function cleanupEntity($entity_id);

  /**
   * Performs cleanup on a run entity revision for the capture utility.
   *
   * @param int $revision_id
   *   Specific revision to cleanup.
   */
  public function cleanupRevision($revision_id);

  /**
   * Retrieves a filename based on the specified data.
   *
   * @param array $data
   *   Capture data array.
   * @param string $extension
   *   File extension of the capture.
   *
   * @return string
   *   Retrieves a filename for a capture.
   */
  public function getFileName(array $data, $extension);

}
