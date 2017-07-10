<?php

namespace Drupal\Tests\web_page_archive\Unit\Mock;

use Drupal\web_page_archive\Plugin\CaptureResponse\HtmlCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtility\HtmlCaptureUtility;

/**
 * Mock html capture utility.
 */
class MockHtmlCaptureUtility extends HtmlCaptureUtility {

  /**
   * Constructor for mock html capture utility.
   */
  public function __construct() {
    parent::__construct([], 'HtmlCaptureUtility', [
      'id' => 'HtmlCaptureUtility',
      'label' => 'Html capture utility',
    ]);
  }

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
