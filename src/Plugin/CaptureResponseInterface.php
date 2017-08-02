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
   * Retrieve capture url.
   *
   * @return url
   *   URL that was captured in this response.
   */
  public function getCaptureUrl();

  /**
   * Retrieve serialized representation of object.
   *
   * @return string
   *   Serialized representation of object.
   */
  public function getSerialized();

  /**
   * Retrieves the size of a capture in bytes.
   *
   * @todo Is there concern with maxint on 32-bit machines?
   *
   * @return int
   *   Total bytes of capture.
   */
  public function getCaptureSize();

}
