<?php

namespace Drupal\Tests\web_page_archive\Kernel\Controller;

use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection;
use Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse;
use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the functionality of the run comparison controller.
 *
 * @group web_page_archive
 */
class RunComparisonControllerTest extends EntityStorageTestBase {

  protected $controller;
  protected $runComparisonStorage;
  protected $runStorage;
  protected $fieldTypeManager;
  protected $fieldFormatterManager;
  protected $entityFieldManager;

  /**
   * Tests RunComparisonController::generateRevisionLabel().
   */
  public function testGenerateRevisionLabel() {
    $label = $this->runComparisonController->generateRevisionLabel(5, 'My Capture Run', 1387637515);
    $expected = 'My Capture Run: 2013-12-21 14:51:55 (5)';
    $this->assertEquals($expected, $label);

    $label = $this->runComparisonController->generateRevisionLabel(993, 'Another Run', 15939944323);
    $expected = 'Another Run: 2475-02-12 02:18:43 (993)';
    $this->assertEquals($expected, $label);
  }

  /**
   * Tests RunComparisonController::handleRunAutocomplete().
   */
  public function testHandleRunAutocomplete() {
    // Create three run entities.
    $entity = $this->getRunEntity('My run entity', 3);
    $entity2 = $this->getRunEntity('Yet another run entity', 2);
    $entity3 = $this->getRunEntity('Non-matching run', 6);

    // Get all revision ids for the first two runs.
    $revision_ids = $this->runStorage->revisionIds($entity);
    $revision_ids_2 = $this->runStorage->revisionIds($entity2);

    // Confirm 3 additional revisions were created (i.e. 4 total).
    $this->assertEquals(4, count($revision_ids));

    // Partial-matching string should return all matching revisions.
    $request = new Request(['q' => 'entity']);
    $response = $this->runComparisonController->handleRunAutocomplete($request);
    $expected = [];
    foreach ($revision_ids as $second => $revision_id) {
      $expected[] = [
        'label' => "My run entity: 2017-12-21 12:40:0{$second} ({$revision_id})",
        'value' => $revision_id,
      ];
    }
    foreach ($revision_ids_2 as $second => $revision_id) {
      $expected[] = [
        'label' => "Yet another run entity: 2017-12-21 12:40:0{$second} ({$revision_id})",
        'value' => $revision_id,
      ];
    }

    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Full string match should return non-empty list.
    $request = new Request(['q' => "My run entity: 2017-12-21 12:40:02 ({$revision_ids[2]})"]);
    $response = $this->runComparisonController->handleRunAutocomplete($request);
    $expected = [
      [
        'label' => "My run entity: 2017-12-21 12:40:02 ({$revision_ids[2]})",
        'value' => $revision_ids[2],
      ],
    ];

    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Non-matching string should return empty list.
    $request = new Request(['q' => 'Invalid String']);
    $response = $this->runComparisonController->handleRunAutocomplete($request);
    $expected = [];
    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Empty string should return empty list.
    $request = new Request(['q' => '']);
    $response = $this->runComparisonController->handleRunAutocomplete($request);
    $expected = [];
    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Unspecified string should return empty list.
    $request = new Request();
    $response = $this->runComparisonController->handleRunAutocomplete($request);
    $expected = [];
    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));
  }

  /**
   * Tests RunComparisionController::stripCaptureKey().
   */
  public function testStripCaptureKey() {
    $tests = [
      /* String-based tests */
      // String does not match.
      [
        'expected' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'string',
        'strip_patterns' => [
          'http://www.fark.com/',
          'http://www.reddit.com/',
        ],
      ],
      // String matches once.
      [
        'expected' => 'memes/how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'string',
        'strip_patterns' => [
          'http://www.fark.com/',
          'http://knowyourmeme.com/',
          'http://www.reddit.com/',
        ],
      ],
      // String matches twice.
      [
        'expected' => 'how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'string',
        'strip_patterns' => [
          'http://www.fark.com/',
          'http://knowyourmeme.com/',
          'memes/',
        ],
      ],
      /* RegEx-based tests */
      // regex does not match.
      [
        'expected' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'regex',
        'strip_patterns' => [
          '.*:\/\/www\.fark\.com\/',
          '.*\:\/\/www\.reddit\.com\/',
        ],
      ],
      // String matches once.
      [
        'expected' => 'memes/how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'regex',
        'strip_patterns' => [
          '.*\:\/\/www\.fark\.com\/',
          '.*\:\/\/knowyourmeme\.com\/',
          '.*\:\/\/www\.reddit\.com\/',
        ],
      ],
      // String matches twice.
      [
        'expected' => 'how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'regex',
        'strip_patterns' => [
          '.*\:\/\/www\.fark.com\/',
          '.*\:\/\/knowyourmeme\.com\/',
          '.*mes\/',
        ],
      ],
      /* Empty tests */
      // Empty type should not strip either string or regex patterns.
      [
        'expected' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => '',
        'strip_patterns' => [
          'http://knowyourmeme.com/',
          '.*\:\/\/knowyourmeme\.com\/',
        ],
      ],
      // Invalid type should not strip either string or regex patterns.
      [
        'expected' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'url' => 'http://knowyourmeme.com/memes/how-is-babby-formed',
        'strip_type' => 'blarg',
        'strip_patterns' => [
          'http://knowyourmeme.com/',
          '.*\:\/\/knowyourmeme\.com\/',
        ],
      ],

    ];

    foreach ($tests as $test) {
      $this->assertEquals($test['expected'], $this->runComparisonController->stripCaptureKey($test['url'], $test['strip_type'], $test['strip_patterns']));
    }
  }

  /**
   * Tests RunComparisionController::generateRunMatrix().
   */
  public function testGenerateRunMatrix() {
    // Create a run entity and then load the first two runs.
    $entity = $this->getRunEntity('My run entity', 2);
    list($run1_id, $run2_id) = $this->runStorage->revisionIds($entity);
    $entity_runs = [
      $this->runStorage->loadRevision($run1_id),
      $this->runStorage->loadRevision($run2_id),
    ];

    // Evaluate a few capture response.
    $capture_results = [
      [
        'capture_response' => new UriCaptureResponse('You can do anything at zombo.com.', 'http://www.zombo.com'),
        'capture_url' => 'http://www.zombo.com',
        'delta' => 4,
      ],
      [
        'capture_response' => new UriCaptureResponse('Find all the things', 'https://www.google.com'),
        'capture_url' => 'https://www.google.com',
        'delta' => 13,
      ],
      [
        'capture_response' => new UriCaptureResponse('Pretend to find all the things', 'https://staging.google.com'),
        'capture_url' => 'https://staging.google.com',
        'delta' => 22,
      ],
    ];

    // Set run capture data.
    $entity_runs[0]->setCapturedArray([
      $this->getCaptureField($capture_results[0]),
      $this->getCaptureField($capture_results[1]),
      $this->getCaptureField($capture_results[2]),
    ])->save();
    $entity_runs[1]->setCapturedArray([
      $this->getCaptureField($capture_results[0]),
    ])->save();

    // Evaluate the expected matrix is generated.
    $expected = [
      'wpa_uri_capture_response' => [
        'http://zombo.com' => [
          $run1_id => [4 => $capture_results[0]],
          $run2_id => [4 => $capture_results[0]],
        ],
        'https://google.com' => [
          $run1_id => [13 => $capture_results[1], 22 => $capture_results[2]],
        ],
      ],
    ];
    $this->assertEquals($expected, $this->runComparisonController->generateRunMatrix($entity_runs, 'string', ['www.', 'staging.']));
  }

  /**
   * Tests RunComparisionController::enqueueRunComparisons().
   */
  public function testEnqueueRunComparisons() {
    // Create a run entity and then load the first two runs.
    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);

    // Evaluate a few capture response.
    $capture_results = [
      [
        'capture_response' => new UriCaptureResponse('You can do anything at zombo.com.', 'http://www.zombo.com'),
        'capture_url' => 'http://www.zombo.com',
        'delta' => 4,
      ],
      [
        'capture_response' => new UriCaptureResponse('Find all the things', 'https://www.google.com'),
        'capture_url' => 'https://staging.google.com',
        'delta' => 13,
      ],
    ];

    // Set run capture data.
    $run_comparison->getRun1()->setCapturedArray([
      $this->getCaptureField($capture_results[0]),
      $this->getCaptureField($capture_results[1]),
    ])->save();
    $run_comparison->getRun2()->setCapturedArray([
      $this->getCaptureField($capture_results[0]),
    ])->save();

    // Enqueue the comparison and make sure there are 2 items in the queue.
    $this->runComparisonController->enqueueRunComparisons($run_comparison);
    $queue = $run_comparison->getQueue();
    $this->assertEquals(2, $queue->numberOfItems());

    $context = [];

    // Attempt to process the next item in the queue.
    $this->assertTrue($this->runComparisonController->batchProcess($run_comparison, $context));
    $this->assertEquals(1, $queue->numberOfItems());
    $this->assertEquals($run_comparison, $context['results']['entity']);

    // Attempt to process the next item in the queue.
    $this->assertTrue($this->runComparisonController->batchProcess($run_comparison));
    $this->assertEquals(0, $queue->numberOfItems());
    $this->assertEquals($run_comparison, $context['results']['entity']);

    // Simulate "batch finish" process.
    $this->runComparisonController->batchFinished(TRUE, $context['results'], []);
    $expected = ['status' => ['The comparison has been completed.']];
    $this->assertEquals($expected, \Drupal::messenger()->all());

    $expected = [
      '1' => [
        'run1' => (string) $run_comparison->getRun1Id(),
        'run2' => (string) $run_comparison->getRun2Id(),
        'delta1' => '4',
        'delta2' => '4',
        'has_left' => '1',
        'has_right' => '1',
        'revision_id' => $run_comparison->getRevisionId(),
        'url' => 'http://zombo.com',
        'variance' => '0',
      ],
      '2' => [
        'run1' => (string) $run_comparison->getRun1Id(),
        'run2' => (string) $run_comparison->getRun2Id(),
        'delta1' => '13',
        'delta2' => '0',
        'has_left' => '1',
        'has_right' => '0',
        'revision_id' => $run_comparison->getRevisionId(),
        'url' => 'https://google.com',
        'variance' => '100',
      ],
    ];
    $this->assertArraySubset($expected, $run_comparison->getResults());
  }

  /**
   * Tests RunComparisonController::markCompareComplete().
   */
  public function testMarkCompareComplete() {
    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $response_collection = new CompareResponseCollection();
    $response_collection->addResponse(new FileSizeVarianceCompareResponse(45));
    $response_collection->addResponse(new FileSizeVarianceCompareResponse(34));
    $this->setMockCompareResults($run_comparison, TRUE, $response_collection);

    $expected = [
      '1' => [
        'run1' => $run_comparison->getRun1Id(),
        'run2' => $run_comparison->getRun2Id(),
        'delta1' => '50',
        'delta2' => '150',
        'has_left' => '1',
        'has_right' => '1',
        'revision_id' => $run_comparison->getRevisionId(),
        'url' => 'http://www.homestarrunner.com',
        'variance' => '53',
      ],
    ];
    $this->assertArraySubset($expected, $run_comparison->getResults());

    // Confirm normalized variance gets added to the database.
    $normalized_variance = $this->runComparisonStorage->getNormalizedVarianceAtIndex($run_comparison->id());
    $expected = [
      (object) [
        'cid' => $run_comparison->id(),
        'response_index' => '0',
        'plugin_id' => 'wpa_file_size_variance_compare_response',
        'variance' => '45',
      ],
      (object) [
        'cid' => $run_comparison->id(),
        'response_index' => '1',
        'plugin_id' => 'wpa_file_size_variance_compare_response',
        'variance' => '34',
      ],
    ];
    $this->assertEquals($expected, $normalized_variance);

  }

  /**
   * Tests RunComparisonController::markCompareComplete().
   *
   * @expectedException Exception
   * @expectedExceptionMessage run2 is required
   */
  public function testMarkCompareCompleteThrowsExceptionIfMissingSecondRun() {
    $strip_patterns = ['www.', 'staging.'];
    $run_comparison = $this->getRunComparisonEntity('Compare job', 'My run entity', 2, 'string', $strip_patterns);
    $this->setMockCompareResults($run_comparison);

    $expected = [
      '1' => [
        'run1' => $run_comparison->getRun1Id(),
        'run2' => $run_comparison->getRun2Id(),
        'delta1' => '50',
        'delta2' => '150',
        'has_left' => '1',
        'has_right' => '0',
        'revision_id' => $run_comparison->getRevisionId(),
        'url' => 'http://www.homestarrunner.com',
        'variance' => '53',
      ],
    ];
    $run_comparison->getResults();
  }

}
