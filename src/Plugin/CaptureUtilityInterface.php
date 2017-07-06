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
   * @param string|null $url
   *   The url to capture.
   *
   * @return CaptureUtilityInterface
   *   Returns reference to self.
   */
  public function captureUrl($url);

  /**
   * Retrieves response from most recent capture.
   *
   * @return Drupal\web_page_archive\Plugin\CaptureResponseInterface
   *   A capture response object.
   */
  public function getResponse();

}
