<?php

namespace Drupal\web_page_archive\Plugin;

/**
 * Defines an interface for Capture responses.
 */
interface CaptureResponseInterface {

  /**
   * Retrieve response type.
   *
   * @return string
   *   String response type.
   */
  public function getType();

  /**
   * Retrieve content.
   *
   * @return string
   *   Stringified content.
   */
  public function getContent();

  /**
   * Retrieve serialized representation of object.
   *
   * @return string
   *   Serialized representation of object.
   */
  public function getSerialized();

}
