<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CaptureResponse;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\wpa_screenshot_capture\Plugin\CaptureResponse\ScreenshotCaptureResponse;

/**
 * Tests the functionality of the screenshot capture response.
 *
 * @group web_page_archive
 */
class ScreenshotCaptureResponseTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'web_page_archive',
    'wpa_screenshot_capture',
  ];

  /**
   * Tests ScreenshotCaptureResponse::compare().
   */
  public function testCompare() {
    $file1 = __DIR__ . '/../../fixtures/drupal-org-1.png';
    $file2 = __DIR__ . '/../../fixtures/drupal-org-2.png';

    $compare_utilities = [
      'web_page_archive_file_size_compare' => 'web_page_archive_file_size_compare',
    ];

    // Assert screenshots have 0.6% file size variance.
    $capture1 = new ScreenshotCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new ScreenshotCaptureResponse($file2, 'http://www.drupal.org/');
    $response = ScreenshotCaptureResponse::compare($capture1, $capture2, $compare_utilities);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection', get_class($response));
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse', get_class($response->getResponses()[0]));
    $this->assertEquals(0.6, $response->getResponses()[0]->getVariance());
  }

}
