<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Capture utility plugins.
 */
interface CaptureUtilityInterface extends PluginInspectionInterface {

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
   * Returns a render array summarizing the configuration of the capture utility.
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

}
