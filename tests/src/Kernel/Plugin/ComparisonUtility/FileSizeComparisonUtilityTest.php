<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\ComparisonUtility;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\wpa_screenshot_capture\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility\FileSizeComparisonUtility;

/**
 * Tests the functionality of the screenshot capture response.
 *
 * @group web_page_archive
 */
class FileSizeComparisonUtilityTest extends EntityKernelTestBase {

  protected $fileSizeComparisonUtility;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $comparison_utility_manager = $this->container->get('plugin.manager.comparison_utility');
    $this->fileSizeComparisonUtility = $comparison_utility_manager->createInstance('web_page_archive_file_size_compare');
  }

  /**
   * Tests ScreenshotCaptureResponse::compare().
   */
  public function testCompare() {
    $file1 = __DIR__ . '/../../fixtures/drupal-org-1.png';
    $file2 = __DIR__ . '/../../fixtures/drupal-org-2.png';

    // Assert same file returns CompareResponseCollection containing
    // SameCompareResponse object.
    $capture1 = new ScreenshotCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new ScreenshotCaptureResponse($file1, 'http://www.drupal.org/');
    $response = $this->fileSizeComparisonUtility->compare($capture1, $capture2);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse', get_class($response));
    $this->assertEquals(0, $response->getVariance());

    // Assert screenshots have 0.6% file size variance.
    $capture1 = new ScreenshotCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new ScreenshotCaptureResponse($file2, 'http://www.drupal.org/');
    $response = $this->fileSizeComparisonUtility->compare($capture1, $capture2);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse', get_class($response));
    $this->assertEquals(0.6, $response->getVariance());

    // Assert screenshots have 0.6% file size variance (reversed order).
    $capture1 = new ScreenshotCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new ScreenshotCaptureResponse($file2, 'http://www.drupal.org/');
    $response = $this->fileSizeComparisonUtility->compare($capture2, $capture1);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse', get_class($response));
    $this->assertEquals(0.6, $response->getVariance());
  }

}
