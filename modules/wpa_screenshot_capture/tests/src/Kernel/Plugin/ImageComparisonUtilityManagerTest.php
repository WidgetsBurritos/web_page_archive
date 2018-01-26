<?php

namespace Drupal\Tests\wpa_screenshot_capture\Kernel\Plugin;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * A base class for kernel tests that can create and store entities.
 */
class ImageComparisonUtilityManagerTest extends EntityKernelTestBase {

  /**
   * String translation context.
   *
   * @var string
   */
  protected $context;

  /**
   * Image comparison utility manager service.
   *
   * @var Drupal\wpa_screenshot_capture\Plugin\ImageComparisonUtilityManager
   */
  protected $imageComparisonManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'wpa_screenshot_capture',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->context = ['context' => 'Web Page Archive'];
    $this->imageComparisonManager = $this->container->get('plugin.manager.wpa_image_comparison_utility');
  }

  /**
   * Tests that expected image comparison utility plugins are defined.
   */
  public function testImageComparisonManagerServiceExists() {
    $expected = [
      'wpa_imagemagick_compare' => [
        'id' => 'wpa_imagemagick_compare',
        'description' => new TranslatableMarkup('Compares images and generates diff images.', [], $this->context),
        'label' => new TranslatableMarkup('ImageMagick', [], $this->context),
        'class' => 'Drupal\wpa_screenshot_capture\Plugin\ImageComparisonUtility\ImageMagickComparisonUtility',
        'provider' => 'wpa_screenshot_capture',
      ],
    ];
    $this->assertEquals($expected, $this->imageComparisonManager->getDefinitions());
  }

  /**
   * Tests methods in abstract ImageComparisonUtilityBase class.
   */
  public function testImageComparisonUtilityBaseMethods() {
    $instance = $this->imageComparisonManager->createInstance('wpa_imagemagick_compare');
    $label = new TranslatableMarkup('ImageMagick', [], $this->context);
    $expected_summary = [
      '#markup' => '',
      '#image_comparison_utility' => [
        'id' => 'wpa_imagemagick_compare',
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
      'id' => 'wpa_imagemagick_compare',
      'weight' => 50,
      'data' => [],
    ];
    $this->assertEquals($expected_configuration, $instance->getConfiguration());
    $instance->setConfiguration([
      'uuid' => 'brand new uuid',
      'id' => 'trying_to_change_but_cant',
      'weight' => 22,
      'data' => ['abc', 'def']
    ]);
    $expected_configuration = [
      'uuid' => 'brand new uuid',
      'id' => 'wpa_imagemagick_compare',
      'weight' => 22,
      'data' => ['abc', 'def'],
    ];
    $this->assertEquals($expected_configuration, $instance->getConfiguration());
  }

}
