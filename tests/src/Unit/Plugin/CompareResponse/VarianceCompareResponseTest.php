<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse
 *
 * @group web_page_archive
 */
class VarianceCompareResponseTest extends UnitTestCase {

  /**
   * Tests that the variance is set by the constructor.
   */
  public function testUnmarkedResponseVarianceIsSetByTheConstructor() {
    $response = new VarianceCompareResponse(53);
    $this->assertEquals(53, $response->getVariance());
  }

}
