<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\ComparisonUtility;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\wpa_screenshot_capture\Plugin\CaptureResponse\ScreenshotCaptureResponse;

/**
 * Tests the functionality of the slider comparison utility.
 *
 * @group web_page_archive
 */
class SliderComparisonUtilityTest extends EntityKernelTestBase {

  protected $pixelComparisonUtility;

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
    $this->sliderComparisonUtility = $comparison_utility_manager->createInstance('wpa_screenshot_capture_slider_compare');
  }

  /**
   * Tests SliderComparisonUtility::compare().
   */
  public function testCompare() {
    $file1 = __DIR__ . '/../../fixtures/drupal-org-1.png';
    $file2 = __DIR__ . '/../../fixtures/drupal-org-2.png';

    // Assert SliderScreenshotCompareResponse variance is always zero.
    $capture1 = new ScreenshotCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new ScreenshotCaptureResponse($file2, 'http://www.drupal.org/');
    $response = $this->sliderComparisonUtility->compare($capture1, $capture2);
    $this->assertEquals('Drupal\wpa_screenshot_capture\Plugin\CompareResponse\SliderScreenshotCompareResponse', get_class($response));
    $this->assertEquals(0, $response->getVariance());
  }

}
