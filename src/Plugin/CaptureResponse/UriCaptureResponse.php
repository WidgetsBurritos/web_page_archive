<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

use Drupal\Component\Diff\Diff;
use Drupal\web_page_archive\Controller\CleanupController;
use Drupal\web_page_archive\Plugin\CaptureResponseBase;
use Drupal\web_page_archive\Plugin\CaptureResponseInterface;

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
  public static function getId() {
    return 'wpa_uri_capture_response';
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
   * Calulates variance based on a edit array from DiffEngine.
   */
  public static function calculateDiffVariance(array $diff_edits) {
    // If both strings are empty, there is 0% variance.
    $counts = [
      'empty' => 0,
      'add' => 1,
      'copy' => 0,
      'change' => 1,
      'delete' => 1,
      'copy-and-change' => 1,
      'copy-change-copy' => 1,
      'copy-change-copy-add' => 1,
      'copy-delete' => 1,
    ];
    $changes = 0;
    $total_ct = count($diff_edits);
    foreach ($diff_edits as $diff_edit) {
      if (isset($counts[$diff_edit->type])) {
        $changes += $counts[$diff_edit->type];
      }
      else {
        // Remove any invalid operations from the calculation.
        $total_ct--;
      }
    }

    return $total_ct > 0 ? 100 * $changes / $total_ct : 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function compare(CaptureResponseInterface $a, CaptureResponseInterface $b) {
    $response_factory = \Drupal::service('web_page_archive.compare.response');
    $a_content = explode(PHP_EOL, $a->getContent());
    $b_content = explode(PHP_EOL, $b->getContent());
    $diff = new Diff($a_content, $b_content);
    if ($diff->isEmpty()) {
      return $response_factory->getSameCompareResponse();
    }
    $variance = static::calculateDiffVariance($diff->getEdits());
    $response = $response_factory->getVarianceCompareResponse($variance);
    $response->setDiff($diff);
    return $response;
  }

  /**
   * Queues all files in the specified revision for deletion.
   */
  public static function cleanupRevision($revision_id) {
    $run_revision = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive_run')
      ->loadRevision($revision_id);

    $captures = $run_revision->get('field_captures');
    $runs_to_remove = [];
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
        $runs_to_remove[] = $unserialized['run_uuid'];
      }
    }

    // Cleanup empty run directories.
    $web_page_archive = $run_revision->getConfigEntity();
    $utilities = $web_page_archive->getCaptureUtilities()->getInstanceIds();
    foreach ($runs_to_remove as $run_to_remove) {
      foreach ($utilities as $utility) {
        $utility_instance = $web_page_archive->getCaptureUtility($utility);
        $file = $utility_instance->storagePath($web_page_archive->id());
        CleanupController::queueDirectoryDelete($run_to_remove);
      }
    }
  }

}
