<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\web_page_archive\Plugin\CaptureResponse\HtmlCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtilityBase;

/**
 * Captures HTML of a remote uri.
 *
 * @CaptureUtility(
 *   id = "HtmlCaptureUtility",
 *   label = @Translation("Html capture utility", context = "Web Page Archive"),
 * )
 */
class HtmlCaptureUtility extends CaptureUtilityBase {

  /**
   * Most recent response.
   *
   * @var string|null
   */
  private $response = NULL;

  /**
   * {@inheritdoc}
   */
  public function capture(array $data = []) {
    // TODO: Do the actual capture.
    $this->response = new HtmlCaptureResponse('<p>Simulated response</p>');

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

}
