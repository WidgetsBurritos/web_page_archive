<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\NoVariantCompareResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\NoVariantCompareResponse
 *
 * @group web_page_archive
 */
class NoVariantCompareResponseTest extends UnitTestCase {

  /**
   * Tests that a right response, can't be left.
   */
  public function testRightResponseCantBeLeft() {
    $response = new NoVariantCompareResponse();
    $response->markLeft();
    $response->markRight();
    $this->assertFalse($response->isLeft());
    $this->assertTrue($response->isRight());
  }

  /**
   * Tests that a left response, can't be right.
   */
  public function testLeftResponseCantBeRight() {
    $response = new NoVariantCompareResponse();
    $response->markRight();
    $response->markLeft();
    $this->assertTrue($response->isLeft());
    $this->assertFalse($response->isRight());
  }

  /**
   * Tests that the variance is 100 if unmarked.
   */
  public function testUnmarkedResponseVarianceIs100() {
    $response = new NoVariantCompareResponse();
    $this->assertEquals(100, $response->getVariance());
  }

  /**
   * Tests that the variance is 100 if marked left.
   */
  public function testLeftResponseVarianceIs100() {
    $response = new NoVariantCompareResponse();
    $response->markLeft();
    $this->assertEquals(100, $response->getVariance());
  }

  /**
   * Tests that the variance is 100 if marked right.
   */
  public function testRightResponseVarianceIs100() {
    $response = new NoVariantCompareResponse();
    $response->markRight();
    $this->assertEquals(100, $response->getVariance());
  }

}
