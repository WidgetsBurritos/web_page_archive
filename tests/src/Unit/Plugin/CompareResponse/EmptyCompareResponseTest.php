<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\EmptyCompareResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\EmptyCompareResponse
 *
 * @group web_page_archive
 */
class EmptyCompareResponseTest extends UnitTestCase {

  /**
   * Tests that the variance is -1.
   */
  public function testVarianceIsNegativeOne() {
    $response = new EmptyCompareResponse();
    $this->assertEquals(-1, $response->getVariance());
  }

}
