<?php

namespace Drupal\Tests\web_page_archive\Unit\Mock;

use Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtility\ScreenshotCaptureUtility;

/**
 * Mock screenshot capture utility.
 */
class MockScreenshotCaptureUtility extends ScreenshotCaptureUtility {

  /**
   * Constructor for mock screenshot capture utility.
   */
  public function __construct() {
    parent::__construct([], 'ScreenshotCaptureUtility', [
      'id' => 'ScreenshotCaptureUtility',
      'label' => 'Screenshot capture utility',
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
    $this->response = new ScreenshotCaptureResponse('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg');

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

}
