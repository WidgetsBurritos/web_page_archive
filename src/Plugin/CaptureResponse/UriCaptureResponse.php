<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

use Drupal\Component\Diff\Diff;
use Drupal\web_page_archive\Controller\CleanupController;
use Drupal\web_page_archive\Plugin\CaptureResponseBase;
use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\CompareResponse\TextDiffTrait;

/**
 * URI capture response.
 */
class UriCaptureResponse extends CaptureResponseBase {

  use TextDiffTrait;

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
   * {@inheritdoc}
   */
  public static function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $compare_utilities, array $tags = [], array $data = []) {
    // If tags were supplied defer to base class behavior.
    if (!empty($tags)) {
      return parent::compare($a, $b, $compare_utilities, $tags, $data);
    }

    // Otherwise do a simple line-by-line comparison.
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
