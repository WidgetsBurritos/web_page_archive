<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

/**
 * Trait for compare responses that use file sizes.
 */
trait FileSizeTrait {

  /**
   * File 1 size in bytes.
   *
   * @var int
   */
  protected $fileSize1 = 0;

  /**
   * File 2 size in bytes.
   *
   * @var int
   */
  protected $fileSize2 = 0;

  /**
   * Sets the file 1 size.
   */
  public function setFile1Size($file_size) {
    $this->fileSize1 = $file_size;
  }

  /**
   * Retrieves the file 1 size.
   */
  public function getFile1Size() {
    return $this->fileSize1;
  }

  /**
   * Sets the file 2 size.
   */
  public function setFile2Size($file_size) {
    $this->fileSize2 = $file_size;
  }

  /**
   * Retrieves the file 2 size.
   */
  public function getFile2Size() {
    return $this->fileSize2;
  }

}
