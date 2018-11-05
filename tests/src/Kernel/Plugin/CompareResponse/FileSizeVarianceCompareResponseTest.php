<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CompareResponse;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse;

/**
 * Tests the functionality of the file size compare response.
 *
 * @group web_page_archive
 */
class FileSizeVarianceCompareResponseTest extends EntityStorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'web_page_archive',
  ];

  /**
   * Tests FileSizeVarianceCompareResponse::renderable().
   */
  public function testPreviewMode() {
    $response = new FileSizeVarianceCompareResponse(45);

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

    $actual = $response->renderable($options);
    $this->assertEquals('Size 1: 142.14 KB', (string) $actual['size1']['#markup']);
    $this->assertEquals('Size 2: 30.85 MB', (string) $actual['size2']['#markup']);
  }

  /**
   * Tests FileSizeVarianceCompareResponse::renderable().
   */
  public function testFullMode() {
    $response = new FileSizeVarianceCompareResponse(45);

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
