<?php

namespace Drupal\web_page_archive\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\web_page_archive\Controller\RunComparisonController;

/**
 * Provides functionality for running compare jobs.
 *
 * @QueueWorker(
 *   id = "web_page_archive_compare",
 *   title = @Translation("Web Page Archive Compare"),
 * )
 */
class CompareQueueWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Check all required keys are provided.
    $required = [
      'compare_class',
      'left_id',
      'right_id',
      'run_comparison',
      'runs',
      'url',
    ];
    foreach ($required as $key) {
      if (!isset($data[$key])) {
        throw new \Exception("$key is required");
      }
    }

    $response_factory = \Drupal::service('web_page_archive.compare.response');

    // Check if left and right are set.
    $data['has_left'] = isset($data['runs'][$data['left_id']]);
    $data['has_right'] = isset($data['runs'][$data['right_id']]);

    // Populate delta values.
    $data['delta1'] = isset($data['runs'][$data['left_id']]) ? array_keys($data['runs'][$data['left_id']])[0] : 0;
    $data['delta2'] = isset($data['runs'][$data['right_id']]) ? array_keys($data['runs'][$data['right_id']])[0] : 0;

    // Initialize comparison utilities array if it doesn't exist.
    $comparison_utilities = $data['run_comparison']->getComparisonUtilities();

    // If has both left and right, we need to perform a comparison.
    if ($data['has_left'] && $data['has_right']) {
      $left_response = reset($data['runs'][$data['left_id']])['capture_response'];
      $right_response = reset($data['runs'][$data['right_id']])['capture_response'];
      $tags = [];
      $response = call_user_func([$data['compare_class'], 'compare'], $left_response, $right_response, $comparison_utilities, $tags, $data);
    }
    elseif ($data['has_left']) {
      $response = $response_factory->getNoVariantCompareResponse()->markLeft();
    }
    elseif ($data['has_right']) {
      $response = $response_factory->getNoVariantCompareResponse()->markRight();
    }
    else {
      throw new \Exception('Invalid comparison detected.');
    }
    $data['variance'] = $response->getVariance();
    $data['compare_response'] = $response;

    RunComparisonController::markCompareComplete($data);
    return $response;
  }

}
