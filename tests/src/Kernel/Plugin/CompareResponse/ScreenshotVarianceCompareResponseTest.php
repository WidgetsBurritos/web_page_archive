<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CompareResponse;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\wpa_screenshot_capture\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\ScreenshotVarianceCompareResponse;

/**
 * Tests the functionality of the html capture response.
 *
 * @group web_page_archive
 */
class ScreenshotVarianceCompareResponseTest extends EntityStorageTestBase {

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   */
  public function testPreviewMode() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->setMockCompareResults($run_comparison, TRUE);

    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'preview',
    ];

    $expected = [
      'link' => ['#type' => 'link'],
      '#attached' => ['library' => ['web_page_archive/admin']],
    ];

    $this->assertArraySubset($expected, $response->renderable($options));
  }

  /**
   * Tests construction of the full render array.
   */
  public function testFullMode() {
    // Get run comparison.
    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);

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
        'variance' => 0.6,
      ],
    ];
    $this->assertArraySubset($expected, $results);

    // Unserialize results to get compare response.
    $unserialized = unserialize($results[1]['results']);
    $response = $unserialized['compare_response'];

    // Evaluate render array.
    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'full',
    ];;

    $expected = [
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
    ];
    $this->assertArraySubset($expected, $response->renderable($options));
  }

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   *
   * @expectedException Exception
   * @expectedExceptionMessage ScreenshotVarianceCompareResponse: Missing comparison entity.
   */
  public function testMissingComparisonThrowsException() {
    $response = new ScreenshotVarianceCompareResponse(45);
    $options = [];
    $response->renderable($options);
  }

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   *
   * @expectedException Exception
   * @expectedExceptionMessage ScreenshotVarianceCompareResponse: Invalid index.
   */
  public function testMissingIndexThrowsException() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);

    $options = ['run_comparison' => $run_comparison];
    $response->renderable($options);
  }

  /**
   * Tests ScreenshotVarianceCompareResponse::renderable().
   *
   * @expectedException Exception
   * @expectedExceptionMessage ScreenshotVarianceCompareResponse: Invalid index.
   */
  public function testInvalidIndexThrowsException() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);

    $options = [
      'run_comparison' => $run_comparison,
      'index' => 'purple',
    ];
    $response->renderable($options);
  }

  /**
   * Tests that missing screenshots throws an exception.
   *
   * @expectedException Exception
   * @expectedExceptionMessage run2 is required
   */
  public function testMissingScreenshotsThrowException() {
    $response = new ScreenshotVarianceCompareResponse(45);

    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->setMockCompareResults($run_comparison);

    $options = [
      'run_comparison' => $run_comparison,
      'index' => 1,
      'mode' => 'full',
    ];

    $response->renderable($options);
  }

}
