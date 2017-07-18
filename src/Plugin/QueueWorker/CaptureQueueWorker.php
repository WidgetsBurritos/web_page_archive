<?php

namespace Drupal\web_page_archive\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

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
   * {@inheritdoc}
   */
  public function processItem($data) {
    try {
      // Check all required keys are provided.
      $required = ['utility', 'url', 'run_uuid', 'run_entity'];
      foreach ($required as $key) {
        if (empty($data[$key])) {
          throw new \Exception("$key is required");
        }
      }

      // Capture the response.
      $data['capture_response'] = $data['utility']->capture($data)->getResponse();
      $data['run_entity']->markCaptureComplete($data);

      return $data['capture_response'];

    }
    catch (\Exception $e) {
      // TODO: What to do here? (future task)
      drupal_set_message($e->getMessage(), 'warning');
    }
  }

}
