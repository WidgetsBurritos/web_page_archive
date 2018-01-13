<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\FileSizeTrait;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\FileSizeTrait
 *
 * @group web_page_archive
 */
class FileSizeTraitTest extends UnitTestCase {

  use FileSizeTrait;

  /**
   * Tests the default file size value is zero.
   */
  public function testDefaultFileSizesAreZero() {
    $this->assertEquals(0, $this->getFile1Size());
    $this->assertEquals(0, $this->getFile2Size());
  }

  /**
   * Tests that file sizes can be set.
   */
  public function testFileSizesAreSettable() {
    $this->setFile1Size(15553);
    $this->setFile2Size(12223);
    $this->assertEquals(15553, $this->getFile1Size());
    $this->assertEquals(12223, $this->getFile2Size());
  }

}
