<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CaptureResponse;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\wpa_html_capture\Plugin\CaptureResponse\HtmlCaptureResponse;

/**
 * Tests the functionality of the html capture response.
 *
 * @group web_page_archive
 */
class HtmlCaptureResponseTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'web_page_archive',
    'wpa_html_capture',
  ];

  /**
   * Tests HtmlCaptureResponse::compare().
   */
  public function testCompare() {
    $file1 = __DIR__ . '/../../fixtures/sample1.html';
    $file2 = __DIR__ . '/../../fixtures/sample2.html';
    $file3 = __DIR__ . '/../../fixtures/sample3.html';
    $file4 = __DIR__ . '/../../fixtures/sample4.html';
    $compare_utilities = ['wpa_html_diff_compare'];

    // Assert same file returns SameCompareResponse object.
    $capture1 = new HtmlCaptureResponse($file1, 'http://www.realultimatepower.net/');
    $capture2 = new HtmlCaptureResponse($file1, 'http://staging.realultimatepower.net/');
    $response = HtmlCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $responses = $response->getResponses();
    $this->assertEquals(1, count($responses));
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse', get_class($responses[0]));
    $this->assertEquals(0, $responses[0]->getVariance());

    // Assert changing 1/10 lines has a 10% variance.
    $capture1 = new HtmlCaptureResponse($file2, 'http://www.realultimatepower.net/');
    $capture2 = new HtmlCaptureResponse($file3, 'http://staging.realultimatepower.net/');
    $response = HtmlCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $responses = $response->getResponses();
    $this->assertEquals(1, count($responses));
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($responses[0]));
    $this->assertEquals(10, $responses[0]->getVariance());

    // Assert going from 10 to 9 lines has 10% variance.
    $capture1 = new HtmlCaptureResponse($file2, 'http://www.realultimatepower.net/');
    $capture2 = new HtmlCaptureResponse($file1, 'http://staging.realultimatepower.net/');
    $response = HtmlCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $responses = $response->getResponses();
    $this->assertEquals(1, count($responses));
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($responses[0]));
    $this->assertEquals(10, $responses[0]->getVariance());

    // Assert going from 9 to 10 lines has 10% variance.
    $capture1 = new HtmlCaptureResponse($file1, 'http://www.realultimatepower.net/');
    $capture2 = new HtmlCaptureResponse($file2, 'http://staging.realultimatepower.net/');
    $response = HtmlCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $responses = $response->getResponses();
    $this->assertEquals(1, count($responses));
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($responses[0]));
    $this->assertEquals(10, $responses[0]->getVariance());

    // Assert completely different files have 100% variance.
    $capture1 = new HtmlCaptureResponse($file1, 'http://www.realultimatepower.net/');
    $capture2 = new HtmlCaptureResponse($file4, 'http://staging.realultimatepower.net/');
    $response = HtmlCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $responses = $response->getResponses();
    $this->assertEquals(1, count($responses));
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($responses[0]));
    $this->assertEquals(100, $responses[0]->getVariance());

    // Assert completely different files have 100% variance in reverse order.
    $capture1 = new HtmlCaptureResponse($file4, 'http://www.realultimatepower.net/');
    $capture2 = new HtmlCaptureResponse($file1, 'http://staging.realultimatepower.net/');
    $response = HtmlCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $responses = $response->getResponses();
    $this->assertEquals(1, count($responses));
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($responses[0]));
    $this->assertEquals(100, $responses[0]->getVariance());
  }

}
