<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CompareResponse;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\ScreenshotVarianceCompareResponse;

/**
 * Tests the functionality of the html capture response.
 *
 * @group web_page_archive
 */
class ScreenshotVarianceCompareResponseTest extends EntityStorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'wpa_screenshot_capture',
  ];

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   */
  public function testMissingComparisonThrowsException() {
    $response = new ScreenshotVarianceCompareResponse(45);
    $options = [];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('ScreenshotVarianceCompareResponse: Missing comparison entity.');
    $response->renderable($options);
  }

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   */
  public function testMissingIndexThrowsException() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('ScreenshotVarianceCompareResponse: Invalid index.');

    $options = ['run_comparison' => $run_comparison];
    $response->renderable($options);
  }

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   */
  public function testInvalidIndexThrowsException() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('ScreenshotVarianceCompareResponse: Invalid index.');
    $options = [
      'run_comparison' => $run_comparison,
      'index' => 'purple',
    ];
    $response->renderable($options);
  }

  /**
   * Tests that missing screenshots throws an exception.
   */
  public function testMissingScreenshotsThrowException() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('run2 is required');
    $this->setMockCompareResults($run_comparison);

    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'full',
    ];

    $response->renderable($options);
  }

}
