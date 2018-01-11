<?php

namespace Drupal\Tests\web_page_archive\Kernel\Controller;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\web_page_archive\Controller\RunComparisonController;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the functionality of the run comparison controller.
 *
 * @group web_page_archive
 */
class RunComparisonControllerTest extends EntityKernelTestBase {

  protected $controller;
  protected $runComparisonStorage;
  protected $runStorage;
  protected $fieldTypeManager;
  protected $fieldFormatterManager;
  protected $entityFieldManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'node',
    'system',
    'views',
    'web_page_archive',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    date_default_timezone_set('Antarctica/Troll');
    $this->installSchema('web_page_archive', 'web_page_archive_capture_details');
    $this->installSchema('web_page_archive', 'web_page_archive_run_comparison_details');
    $this->installEntitySchema('web_page_archive_run');
    $this->installEntitySchema('wpa_run_comparison');
    $this->installConfig(['web_page_archive']);
    $this->controller = RunComparisonController::create($this->container);
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->runStorage = $entity_type_manager->getStorage('web_page_archive_run');
    $this->runComparisonStorage = $entity_type_manager->getStorage('wpa_run_comparison');
    $this->fieldTypeManager = $this->container->get('plugin.manager.field.field_type');
    $this->fieldFormatterManager = $this->container->get('plugin.manager.field.formatter');
    $this->entityFieldManager = $this->container->get('entity_field.manager');
  }

  /**
   * Creates a run entity with the specified number of revisions.
   */
  private function getRunEntity($name, $revision_ct, $start_time = 1513860000) {
    // Create initial entity.
    $data = [
      'name' => $name,
      'success_ct' => 5,
    ];
    $run_entity = $this->runStorage->create($data);
    $run_entity->setRevisionCreationTime($start_time);
    $run_entity->save();

    // Create additional revisions.
    for ($i = 1; $i <= $revision_ct; $i++) {
      $run_entity->setNewRevision(TRUE);
      $run_entity->setRevisionCreationTime($start_time + $i);
      $run_entity->save();
    }

    return $run_entity;
  }

  /**
   * Creates a capture field with the specified results.
   */
  private function getCaptureField($value) {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions('web_page_archive_run', 'web_page_archive_run');
    $configuration = [
      'name' => 'field_captures',
      'parent' => NULL,
      'field_definition' => $field_definitions['field_captures'],
    ];
    $field = $this->fieldTypeManager->createInstance('web_page_archive_capture', $configuration);

    $field->setValue($value);
    return serialize($field->getValue());
  }

  /**
   * Tests RunComparisonController::generateRevisionLabel().
   */
  public function testGenerateRevisionLabel() {
    $label = $this->controller->generateRevisionLabel(5, 'My Capture Run', 1387637515);
    $expected = 'My Capture Run: 2013-12-21 14:51:55 (5)';
    $this->assertEquals($expected, $label);

    $label = $this->controller->generateRevisionLabel(993, 'Another Run', 15939944323);
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
    $response = $this->controller->handleRunAutocomplete($request);
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
    $response = $this->controller->handleRunAutocomplete($request);
    $expected = [
      [
        'label' => "My run entity: 2017-12-21 12:40:02 ({$revision_ids[2]})",
        'value' => $revision_ids[2],
      ],
    ];

    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Non-matching string should return empty list.
    $request = new Request(['q' => 'Invalid String']);
    $response = $this->controller->handleRunAutocomplete($request);
    $expected = [];
    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Empty string should return empty list.
    $request = new Request(['q' => '']);
    $response = $this->controller->handleRunAutocomplete($request);
    $expected = [];
    $this->assertEquals($expected, json_decode($response->getContent(), TRUE));

    // Unspecified string should return empty list.
    $request = new Request();
    $response = $this->controller->handleRunAutocomplete($request);
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
      $this->assertEquals($test['expected'], $this->controller->stripCaptureKey($test['url'], $test['strip_type'], $test['strip_patterns']));
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
    $this->assertEquals($expected, $this->controller->generateRunMatrix($entity_runs, 'string', ['www.', 'staging.']));
  }

  /**
   * Tests RunComparisionController::enqueueRunComparisons().
   */
  public function testEnqueueRunComparisons() {
    // Create a run entity and then load the first two runs.
    $entity = $this->getRunEntity('My run entity', 2);
    list($run1_id, $run2_id) = $this->runStorage->revisionIds($entity);

    // Create a run comparison.
    $data = [
      'run1' => $run1_id,
      'run2' => $run2_id,
      'name' => 'Compare job',
      'strip_type' => 'string',
      'strip_patterns' => serialize(['www.', 'staging.']),
    ];
    $run_comparison = $this->runComparisonStorage->create($data);
    $run_comparison->save();

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
    $this->controller->enqueueRunComparisons($run_comparison);
    $queue = $run_comparison->getQueue();
    $this->assertEquals(2, $queue->numberOfItems());

    // Attempt to process the next item in the queue.
    $this->assertTrue($this->controller->batchProcess($run_comparison));
    $this->assertEquals(1, $queue->numberOfItems());

    // Attempt to process the next item in the queue.
    $this->assertTrue($this->controller->batchProcess($run_comparison));
    $this->assertEquals(0, $queue->numberOfItems());

    $expected = [
      [
        'run1' => $run1_id,
        'run2' => $run2_id,
        'delta1' => '4',
        'delta2' => '4',
        'has_left' => '1',
        'has_right' => '1',
        'revision_id' => $run_comparison->getRevisionId(),
        'url' => 'http://zombo.com',
        'variance' => '0',
      ],
      [
        'run1' => $run1_id,
        'run2' => $run2_id,
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
    // Create a run comparison.
    $data = [
      'run1' => 47,
      'run2' => 93,
      'name' => 'Compare job',
      'strip_type' => 'string',
      'strip_patterns' => serialize(['www.', 'staging.']),
    ];
    $run_comparison = $this->runComparisonStorage->create($data);
    $run_comparison->save();

    // Create initial array.
    $data = [
      'url' => 'http://www.homestarrunner.com',
      'delta1' => 50,
      'delta2' => 150,
      'has_left' => TRUE,
      'has_right' => FALSE,
      'left_id' => 47,
      'right_id' => 93,
      'run_comparison' => $run_comparison,
      'variance' => 53,
    ];
    $this->controller->markCompareComplete($data);

    $expected = [
      [
        'run1' => 47,
        'run2' => 93,
        'delta1' => '50',
        'delta2' => '150',
        'has_left' => '1',
        'has_right' => '0',
        'revision_id' => $run_comparison->getRevisionId(),
        'url' => 'http://www.homestarrunner.com',
        'variance' => '53',
      ],
    ];
    $this->assertArraySubset($expected, $run_comparison->getResults());
  }

}
