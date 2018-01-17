<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\TextDiffTrait;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\TextDiffTrait
 *
 * @group web_page_archive
 */
class TextDiffTraitTest extends UnitTestCase {

  use TextDiffTrait;

  /**
   * Tests the default file size value is zero.
   */
  public function testDefaultDiffIsEmptyArray() {
    $this->assertEquals([], $this->getDiff());
  }

  /**
   * Tests that file sizes can be set.
   */
  public function testFileSizesAreSettable() {
    $this->setDiff(['a', 'b' => 'c']);
    $this->assertEquals(['a', 'b' => 'c'], $this->getDiff());
  }

}
