<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\CompareResponse;

use Drupal\Component\Diff\Diff;
use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection;
use Drupal\web_page_archive\Plugin\CompareResponse\EmptyCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse;

/**
 * Tests the functionality of the html capture response.
 *
 * @group web_page_archive
 */
class CompareResponseCollectionTest extends EntityStorageTestBase {

  /**
   * Tests preview mode.
   */
  public function testPreviewMode() {
    $response_collection = new CompareResponseCollection();
    $response1 = new EmptyCompareResponse();
    $response2 = new SameCompareResponse();
    $response3 = new VarianceCompareResponse(33);
    $response_collection->addResponse($response1);
    $response_collection->addResponse($response2);
    $response_collection->addResponse($response3);
    $diff = new Diff(['a', 'b', 'c'], ['a', 'd', 'c']);
    $response3->setDiff($diff);
    $expected = [
      ['#markup' => 'No comparison could be performed.'],
      ['#markup' => 'Captures are identical.'],
      [
        '#attached' => ['library' => ['web_page_archive/admin']],
        'link' => ['#title' => 'Display'],
        'size1' => ['#markup' => 'Size 1: 139.09 KB'],
        'size2' => ['#markup' => 'Size 2: 3.37 MB'],
      ],
    ];
    $data = [];
    $run_comparison = $this->container->get('entity_type.manager')->getStorage('wpa_run_comparison')->create($data);
    $options = [
      'index' => 0,
      'delta1' => 3,
      'delta2' => 5,
      'mode' => 'preview',
      'run_comparison' => $run_comparison,
      'runs' => [
        [3 => ['capture_size' => 142432]],
        [5 => ['capture_size' => 3532235]],
      ],
    ];
    $this->assertArraySubset($expected, $response_collection->renderable($options));
  }

  /**
   * Tests full mode.
   */
  public function testFullMode() {
    $response_collection = new CompareResponseCollection();
    $response1 = new EmptyCompareResponse();
    $response2 = new SameCompareResponse();
    $response3 = new VarianceCompareResponse(33);
    $diff = new Diff(['a', 'b', 'c'], ['a', 'd', 'c']);
    $response3->setDiff($diff);
    $response_collection->addResponse($response1);
    $response_collection->addResponse($response2);
    $response_collection->addResponse($response3);
    $expected = [
      ['#markup' => 'No comparison could be performed.'],
      ['#markup' => 'Captures are identical.'],
      [
        '#attached' => [
          'library' => ['web_page_archive/diff'],
        ],
        'diff' => [
          '#type' => 'table',
          '#attributes' => ['class' => ['wpa-diff']],
          '#header' => [['data' => 'Run #1'], ['data' => 'Run #2']],
          '#rows' => [
            [
              ['data' => '-'],
              ['data' => ['#markup' => '<span class="diffchange">b</span>']],
              ['data' => '+'],
              ['data' => ['#markup' => '<span class="diffchange">d</span>']],
            ],
          ],
        ],
      ],
    ];
    $options = ['mode' => 'full'];
    $this->assertArraySubset($expected, $response_collection->renderable($options));
  }

}
