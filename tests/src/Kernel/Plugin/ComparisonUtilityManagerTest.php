<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests comparison utility management.
 *
 * @group web_page_archive
 */
class ComparisonUtilityManagerTest extends EntityKernelTestBase {

  /**
   * String translation context.
   *
   * @var string
   */
  protected $context;

  /**
   * Image comparison utility manager service.
   *
   * @var Drupal\web_page_archive\Plugin\ComparisonUtilityManager
   */
  protected $imageComparisonManager;

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
  protected function setUp() {
    parent::setUp();

    $this->context = ['context' => 'Web Page Archive'];
    $this->imageComparisonManager = $this->container->get('plugin.manager.comparison_utility');
  }

  /**
   * Tests that expected image comparison utility plugins are defined.
   */
  public function testImageComparisonManagerServiceExists() {
    $expected = [
      'wpa_screenshot_capture_imagemagick_compare' => [
        'id' => 'wpa_screenshot_capture_imagemagick_compare',
        'description' => new TranslatableMarkup('Compares images and generates diff images.', [], $this->context),
        'label' => new TranslatableMarkup('Screenshot: ImageMagick', [], $this->context),
        'class' => 'Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility\ImageMagickComparisonUtility',
        'provider' => 'wpa_screenshot_capture',
      ],
      'wpa_screenshot_capture_file_size_compare' => [
        'id' => 'wpa_screenshot_capture_file_size_compare',
        'description' => new TranslatableMarkup('Compares images based on file size.', [], $this->context),
        'label' => new TranslatableMarkup('Screenshot: File Size', [], $this->context),
        'class' => 'Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility\FileSizeComparisonUtility',
        'provider' => 'wpa_screenshot_capture',
      ],
    ];
    $this->assertArraySubset($expected, $this->imageComparisonManager->getDefinitions());
  }

  /**
   * Tests methods in abstract ComparisonUtilityBase class.
   */
  public function testComparisonUtilityBaseMethods() {
    $instance = $this->imageComparisonManager->createInstance('wpa_screenshot_capture_imagemagick_compare');
    $label = new TranslatableMarkup('Screenshot: ImageMagick', [], $this->context);
    $expected_summary = [
      '#markup' => '',
      '#comparison_utility' => [
        'id' => 'wpa_screenshot_capture_imagemagick_compare',
        'description' => new TranslatableMarkup('Compares images and generates diff images.', [], $this->context),
        'label' => $label,
      ],
    ];
    $this->assertEquals($expected_summary, $instance->getSummary());
    $this->assertEquals($label, $instance->label());
    $instance->setWeight(50);
    $this->assertEquals(50, $instance->getWeight());
    $expected_configuration = [
      'uuid' => $instance->getUuid(),
      'id' => 'wpa_screenshot_capture_imagemagick_compare',
      'weight' => 50,
      'data' => [],
    ];
    $this->assertEquals($expected_configuration, $instance->getConfiguration());
    $instance->setConfiguration([
      'uuid' => 'brand new uuid',
      'id' => 'trying_to_change_but_cant',
      'weight' => 22,
      'data' => ['abc', 'def'],
    ]);
    $expected_configuration = [
      'uuid' => 'brand new uuid',
      'id' => 'wpa_screenshot_capture_imagemagick_compare',
      'weight' => 22,
      'data' => ['abc', 'def'],
    ];
    $this->assertEquals($expected_configuration, $instance->getConfiguration());
  }

}
