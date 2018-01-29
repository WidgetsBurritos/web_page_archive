<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CaptureResponse;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;

/**
 * Tests the functionality of the run comparison controller.
 *
 * @group web_page_archive
 */
class UriCaptureResponseTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'web_page_archive',
  ];

  /**
   * Tests UriCaptureResponse::compare().
   */
  public function testCompare() {
    $str1 = 'Are you ready to get pumped?';
    $str2 = 'Are you ready to get pumped?' . PHP_EOL . 'Click "Yes" if yes.';
    $str3 = 'Are you ready to get pumped?' . PHP_EOL . 'Click "Yes" if yes. Click "No" if you\'re a little baby.';
    $str4 = 'This is something completely different';
    $compare_utilities = [];

    // Assert same string returns SameCompareResponse object.
    $capture1 = new UriCaptureResponse($str1, 'http://www.realultimatepower.net/');
    $capture2 = new UriCaptureResponse($str1, 'http://staging.realultimatepower.net/');
    $response = UriCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse', get_class($response));
    $this->assertEquals(0, $response->getVariance());

    // Assert changing one of two lines has a 50% variance.
    $capture1 = new UriCaptureResponse($str2, 'http://www.realultimatepower.net/');
    $capture2 = new UriCaptureResponse($str3, 'http://staging.realultimatepower.net/');
    $response = UriCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse', get_class($response));
    $this->assertEquals(50, $response->getVariance());

    // Assert removing a line from two lines has a 50% variance.
    $capture1 = new UriCaptureResponse($str2, 'http://www.realultimatepower.net/');
    $capture2 = new UriCaptureResponse($str1, 'http://staging.realultimatepower.net/');
    $response = UriCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse', get_class($response));
    $this->assertEquals(50, $response->getVariance());

    // Assert adding a line to a single line has a 50% variance.
    $capture1 = new UriCaptureResponse($str1, 'http://www.realultimatepower.net/');
    $capture2 = new UriCaptureResponse($str2, 'http://staging.realultimatepower.net/');
    $response = UriCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse', get_class($response));
    $this->assertEquals(50, $response->getVariance());

    // Assert completely different strings have 100% variance.
    $capture1 = new UriCaptureResponse($str1, 'http://www.realultimatepower.net/');
    $capture2 = new UriCaptureResponse($str4, 'http://staging.realultimatepower.net/');
    $response = UriCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse', get_class($response));
    $this->assertEquals(100, $response->getVariance());
  }

}
