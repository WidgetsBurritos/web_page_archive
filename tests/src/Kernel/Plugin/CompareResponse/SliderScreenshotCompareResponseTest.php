<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CompareResponse;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\wpa_screenshot_capture\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\SliderScreenshotCompareResponse;

/**
 * Tests the functionality slider compare response.
 *
 * @group web_page_archive
 */
class SliderScreenshotCompareResponseTest extends EntityStorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'wpa_screenshot_capture',
  ];

  /**
   * Tests SliderScreenshotCompareResponse::renderable().
   */
  public function testPreviewMode() {
    $response = new SliderScreenshotCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->setMockCompareResults($run_comparison, TRUE, $response);

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

    $expected = [
      'link' => [
        '#type' => 'link',
        '#title' => 'Compare Images',
        '#attributes' => [
          'class' => ['use-ajax', 'button', 'button--small', 'button--primary'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => '{"width":1280}',
        ],
      ],
      '#attached' => ['library' => ['web_page_archive/admin']],
    ];

    $actual = $response->renderable($options);
    $this->assertArraySubset($expected, $actual);
    $this->assertEquals('admin/config/system/web-page-archive/modal/compare/1/1', $actual['link']['#url']->getInternalPath());
  }

  /**
   * Tests SliderScreenshotCompareResponse::renderable().
   */
  public function testFullMode() {
    // Get run comparison.
    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $run_comparison->set('comparison_utilities', serialize(['wpa_screenshot_capture_slider_compare' => 'wpa_screenshot_capture_slider_compare']));

    // Setup run capture data.
    $run1 = $run_comparison->getRun1();
    $captured = $run1->getCapturedArray();
    $captured->appendItem(serialize([
      'uuid' => '1c0b906e-243d-4c24-a9b7-a0c6ac409d16',
      'run_uuid' => '375791bf-d777-4d42-8398-7953add007bd',
      'timestamp' => '1234000000',
      'status' => 'complete',
      'capture_url' => 'https://www.drupal.org',
      'capture_response' => new ScreenshotCaptureResponse(__DIR__ . '/../../fixtures/drupal-org-1.png', 'https://www.drupal.org'),
      'capture_size' => filesize(__DIR__ . '/../../fixtures/drupal-org-1.png'),
      'vid' => 1,
      'delta' => 0,
      'langcode' => 'pt',
    ]));
    $run1->save();
    $run2 = $run_comparison->getRun2();
    $captured = $run2->getCapturedArray();
    $captured->appendItem(serialize([
      'uuid' => '375791bf-d777-4d42-8398-7953add007bd',
      'run_uuid' => '1c0b906e-243d-4c24-a9b7-a0c6ac409d16',
      'timestamp' => '1234000000',
      'status' => 'complete',
      'capture_url' => 'https://www.drupal.org',
      'capture_response' => new ScreenshotCaptureResponse(__DIR__ . '/../../fixtures/drupal-org-2.png', 'https://www.drupal.org'),
      'capture_size' => filesize(__DIR__ . '/../../fixtures/drupal-org-2.png'),
      'vid' => 1,
      'delta' => 0,
      'langcode' => 'pt',
    ]));
    $run2->save();

    // Enqueue and run the comparison job.
    $this->runComparisonController->enqueueRunComparisons($run_comparison);
    $queue = $run_comparison->getQueue();
    $this->assertEquals(1, $queue->numberOfItems());
    $this->assertTrue($this->runComparisonController->batchProcess($run_comparison));
    $this->assertEquals(0, $queue->numberOfItems());

    // Evaluate results.
    $results = $run_comparison->getResults();
    $expected = [
      1 => [
        'run1' => $run_comparison->getRun1Id(),
        'run2' => $run_comparison->getRun2Id(),
        'delta1' => '0',
        'delta2' => '0',
        'has_left' => '1',
        'has_right' => '1',
        'url' => 'https://drupal.org',
      ],
    ];
    $this->assertArraySubset($expected, $results);

    // Unserialize results to get compare response.
    $unserialized = unserialize($results[1]['results']);
    $response_collection = $unserialized['compare_response'];

    // Evaluate render array.
    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'full',
    ];

    $expected = [
      [
        '#theme' => 'wpa_screenshot_compare',
        '#left' => [
          '#theme' => 'image_style',
          '#style_name' => 'web_page_archive_full',
          '#uri' => __DIR__ . '/../../fixtures/drupal-org-1.png',
        ],
        '#right' => [
          '#theme' => 'image_style',
          '#style_name' => 'web_page_archive_full',
          '#uri' => __DIR__ . '/../../fixtures/drupal-org-2.png',
        ],
      ],
    ];
    $this->assertArraySubset($expected, $response_collection->renderable($options));
  }

}
