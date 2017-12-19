<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse
 *
 * @group web_page_archive
 */
class SameCompareResponseTest extends UnitTestCase {

  /**
   * Tests that the variance is 0.
   */
  public function testVarianceIsZero() {
    $response = new SameCompareResponse();
    $this->assertEquals(0, $response->getVariance());
  }

}
