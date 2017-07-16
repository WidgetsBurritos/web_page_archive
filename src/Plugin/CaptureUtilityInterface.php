<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\web_page_archive\Entity\WebPageArchive;

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
   * Adds fields to config form. The toggle checkbox key should be the id of
   * the capture utility plugin. It is recommending that all form fields are
   * prefixed with the plugin id to reduce risk of duplicate fields.
   *
   * @param array
   *   Form array.
   *
   * @return array
   *   Modified form array.
   */
  public function addConfigFormFields(array $form, WebPageArchive $web_page_archive = NULL);

}
