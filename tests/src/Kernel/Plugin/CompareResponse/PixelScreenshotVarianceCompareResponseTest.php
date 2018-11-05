<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CompareResponse;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\PixelScreenshotVarianceCompareResponse;

/**
 * Tests the functionality of the pixel compare response.
 *
 * @group web_page_archive
 */
class PixelScreenshotVarianceCompareResponseTest extends EntityStorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'wpa_screenshot_capture',
  ];

  /**
   * Tests PixelScreenshotVarianceCompareResponse::renderable().
   */
  public function testPreviewMode() {
    $valid_path = __DIR__ . '/../../fixtures/drupal-org-1.png';
    $empty_response = new PixelScreenshotVarianceCompareResponse(45, '');
    $invalid_response = new PixelScreenshotVarianceCompareResponse(45, '/path/to/nowhere');
    $valid_response = new PixelScreenshotVarianceCompareResponse(45, $valid_path);
    $response_collection = new CompareResponseCollection();
    $response_collection->addResponse($empty_response);
    $response_collection->addResponse($invalid_response);
    $response_collection->addResponse($valid_response);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->setMockCompareResults($run_comparison, TRUE, $response_collection);

    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'preview',
      'delta1' => 3,
      'delta2' => 5,
      'runs' => [
        [3 => ['capture_size' => 145553]],
        [5 => ['capture_size' => 32343352]],
      ],
    ];

    $this->assertEquals('Could not display pixel comparision. Please check your ImageMagick settings.', (string) $empty_response->renderable($options)['pixel']['#markup']);
    $this->assertEquals('Could not display pixel comparision. Please check your ImageMagick settings.', (string) $invalid_response->renderable($options)['pixel']['#markup']);
    $actual = $valid_response->renderable($options);
    $expected = [
      'pixel' => [
        '#theme' => 'image_style',
        '#style_name' => 'web_page_archive_thumbnail',
        '#uri' => $valid_path,
        '#attached' => ['library' => ['web_page_archive/admin']],
        '#prefix' => '<div class="wpa-comparison-pixel">',
        '#suffix' => '</div>',
      ],
      'variance' => [
        '#prefix' => '<div class="wpa-comparison-variance">',
        '#markup' => 'Pixel Variance: 45%',
        '#suffix' => '</div>',
      ],
    ];
    $this->assertEquals($expected['pixel'], $actual['pixel']);
    $this->assertEquals($expected['variance'], $actual['variance']);
  }

  /**
   * Tests PixelScreenshotVarianceCompareResponse::renderable().
   */
  public function testFullMode() {
    $valid_path = __DIR__ . '/../../fixtures/drupal-org-1.png';
    $response = new PixelScreenshotVarianceCompareResponse(45, $valid_path);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->setMockCompareResults($run_comparison, TRUE, $response);

    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'full',
      'delta1' => 3,
      'delta2' => 5,
      'runs' => [
        [3 => ['capture_size' => 145553]],
        [5 => ['capture_size' => 32343352]],
      ],
    ];

    $this->assertEquals([], $response->renderable($options));
  }

}
