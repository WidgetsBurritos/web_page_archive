<?php

namespace Drupal\web_page_archive\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\web_page_archive\Plugin\CaptureUtilityManager;

/**
 * Provides functionality for running capture jobs.
 *
 * @QueueWorker(
 *   id = "web_page_archive_capture",
 *   title = @Translation("Web Page Archive Capture"),
 * )
 */
class CaptureQueueWorker extends QueueWorkerBase {

  /**
   * Capture Utility Manager Service.
   *
   * @var \Drupal\web_page_archive\Plugin\CaptureUtilityManager
   */
  protected $captureUtilityManager = NULL;

  /**
   * Retrieves the capture utility manager service.
   *
   * @return Drupal\web_page_archive\Plugin\CaptureUtilityManager
   *   Capture utility manager service.
   */
  public function getCaptureUtilityManager() {
    return $this->captureUtilityManager ?: \Drupal::service('plugin.manager.capture_utility');
  }

  /**
   * Sets the capture utility manager service.
   *
   * @param \Drupal\web_page_archive\Plugin\CaptureUtilityManager $capture_utility_manager
   *   Capture utility manager service.
   */
  public function setCaptureUtilityManager(CaptureUtilityManager $capture_utility_manager) {
    $this->captureUtilityManager = $capture_utility_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Check all required keys are provided.
    $required = ['url', 'run_uuid', 'run_entity', 'user_agent'];
    foreach ($required as $key) {
      if (empty($data[$key])) {
        throw new \Exception("$key is required");
      }
    }

    if (!isset($data['utility'])) {
      if (!isset($data['utility_plugin_id']) || !isset($data['utility_plugin_configuration'])) {
        throw new \Exception('utility_plugin_id and utility_plugin_configuration are required');
      }
      $data['utility'] = $this->getCaptureUtilityManager()->createInstance($data['utility_plugin_id'], json_decode($data['utility_plugin_configuration'], TRUE));
    }

    // Capture the response.
    $data['capture_response'] = $data['utility']->capture($data)->getResponse();
    if (isset($data['capture_response'])) {
      $data['run_entity']->markCaptureComplete($data);
    }

    return $data['capture_response'];
  }

}
