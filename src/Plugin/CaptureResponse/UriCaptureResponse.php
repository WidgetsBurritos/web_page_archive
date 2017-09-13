<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

use Drupal\web_page_archive\Controller\CleanupController;
use Drupal\web_page_archive\Plugin\CaptureResponseBase;

/**
 * URI capture response.
 */
class UriCaptureResponse extends CaptureResponseBase {

  /**
   * UriCaptureResponse constructor.
   *
   * @param string $content
   *   The response contents.
   * @param string $capture_url
   *   URL that is getting captured.
   */
  public function __construct($content, $capture_url) {
    $this->setType('uri')
      ->setContent($content)
      ->setCaptureUrl($capture_url);
  }

  /**
   * {@inheritdoc}
   */
  public function getCaptureSize() {
    // TODO: What to do if remote URL instead of local file path?
    if (!is_readable($this->getContent())) {
      throw new \Exception("Can't read file.");
    }
    return filesize($this->getContent());
  }

  /**
   * {@inheritdoc}
   */
  public function renderable(array $options = []) {
    return $this->content;
  }

  /**
   * Queues all files in the specified revision for deletion.
   */
  public static function cleanupRevision($revision_id) {
    $run_revision = \Drupal::entityManager()
      ->getStorage('web_page_archive_run')
      ->loadRevision($revision_id);

    $captures = $run_revision->get('field_captures');
    foreach ($captures as $capture) {
      // Skip invalid responses, which indicates there are no files to remove.
      $value = $capture->getValue();
      if (empty($value['value'])) {
        continue;
      }
      $unserialized = unserialize($value['value']);
      if (empty($unserialized['capture_response'])) {
        continue;
      }

      // Queue up file for removal.
      $file = $unserialized['capture_response']->getContent();
      if (file_exists($file)) {
        CleanupController::queueFileDelete($file);
      }
    }
  }

}
