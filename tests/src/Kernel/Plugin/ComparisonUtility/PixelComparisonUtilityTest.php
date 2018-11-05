<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\ComparisonUtility;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the functionality of the pixel comparison utility.
 *
 * @group web_page_archive
 */
class PixelComparisonUtilityTest extends EntityKernelTestBase {

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
    $this->pixelComparisonUtility = $comparison_utility_manager->createInstance('wpa_screenshot_capture_pixel_compare');
  }

  /**
   * Tests PixelComparisonUtility::getFilterCriteria().
   */
  public function testGetFilterCriteria() {
    $expected = ['wpa_pixel_screenshot_variance_compare_response' => 'Screenshot: Pixel'];
    $this->assertEquals($expected, $this->pixelComparisonUtility->getFilterCriteria());
  }

}
