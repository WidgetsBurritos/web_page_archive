<?php

namespace Drupal\Tests\web_page_archive\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\web_page_archive\Controller\RunComparisonController;
use Drupal\web_page_archive\Controller\WebPageArchiveController;
use Drupal\web_page_archive\Entity\RunComparison;
use Drupal\web_page_archive\Plugin\CompareResponseInterface;

/**
 * A base class for kernel tests that can create and store entities.
 */
abstract class EntityStorageTestBase extends EntityKernelTestBase {

  protected $entityFieldManager;
  protected $entityTypeManager;
  protected $fieldFormatterManager;
  protected $fieldTypeManager;
  protected $runComparisonController;
  protected $runComparisonStorage;
  protected $runStorage;
  protected $webPageArchiveController;

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
    'wpa_skeleton_capture',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Set a timezone for tests.
    date_default_timezone_set('Antarctica/Troll');

    // Install schemas and config.
    $this->installSchema('web_page_archive', 'web_page_archive_capture_details');
    $this->installSchema('web_page_archive', 'web_page_archive_run_comparison_details');
    $this->installSchema('web_page_archive', 'web_page_archive_comparison_variance');
    $this->installEntitySchema('web_page_archive_run');
    $this->installEntitySchema('wpa_run_comparison');
    $this->installConfig(['web_page_archive']);

    // Setup services.
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->wpaStorage = $this->entityTypeManager->getStorage('web_page_archive');
    $this->runStorage = $this->entityTypeManager->getStorage('web_page_archive_run');
    $this->runComparisonStorage = $this->entityTypeManager->getStorage('wpa_run_comparison');
    $this->fieldTypeManager = $this->container->get('plugin.manager.field.field_type');
    $this->fieldFormatterManager = $this->container->get('plugin.manager.field.formatter');
    $this->entityFieldManager = $this->container->get('entity_field.manager');
    $this->runComparisonController = RunComparisonController::create($this->container);
    $this->webPageArchiveController = WebPageArchiveController::create($this->container);
  }

  /**
   * Creates a web_page_archive entity.
   */
  protected function getWpaEntity($name, $urls = []) {
    $run_entity = $this->getRunEntity($name, 1);
    $data = [
      'id' => md5($name),
      'capture_utilities' => [
        '3f6a4941-ce49-41cf-b779-dad4abaca300' => [
          'uuid' => '3f6a4941-ce49-41cf-b779-dad4abaca300',
          'id' => 'wpa_skeleton_capture',
          'weight' => 1,
        ],
      ],
      'name' => $name,
      'urls' => implode(PHP_EOL, $urls),
      'run_entity' => $run_entity->id(),
      'use_robots' => FALSE,
    ];

    $entity = $this->wpaStorage->create($data);
    $entity->save();

    return $entity;
  }

  /**
   * Creates a web_page_archive_run with the specified number of revisions.
   */
  protected function getRunEntity($name, $revision_ct, $start_time = 1513860000) {
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
   * Creates a wpa_run_comparison entity.
   */
  protected function getRunComparisonEntity($compare_name, $run_name, $run_revision_ct, $strip_type = '', $strip_patterns = []) {
    $entity = $this->getRunEntity($run_name, $run_revision_ct);
    list($run1_id, $run2_id) = $this->runStorage->revisionIds($entity);

    // Create a run comparison.
    $data = [
      'run1' => $run1_id,
      'run2' => $run2_id,
      'name' => $compare_name,
      'strip_type' => $strip_type,
      'strip_patterns' => serialize($strip_patterns),
    ];
    $run_comparison = $this->runComparisonStorage->create($data);
    $run_comparison->save();

    return $run_comparison;
  }

  /**
   * Creates a capture field with the specified results.
   */
  protected function getCaptureField($value) {
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
   * Sets mock compare results.
   */
  protected function setMockCompareResults(RunComparison $run_comparison, $has_right = FALSE, CompareResponseInterface $compare_response = NULL) {
    // Create initial array.
    $data = [
      'url' => 'http://www.homestarrunner.com',
      'delta1' => 50,
      'delta2' => 150,
      'has_left' => TRUE,
      'has_right' => $has_right,
      'left_id' => $run_comparison->getRun1Id(),
      'right_id' => $has_right ? $run_comparison->getRun2Id() : NULL,
      'run_comparison' => $run_comparison,
      'variance' => 53,
      'compare_response' => $compare_response,
    ];
    $this->runComparisonController->markCompareComplete($data);
  }

}
