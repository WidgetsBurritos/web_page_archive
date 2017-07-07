<?php

namespace Drupal\Tests\web_page_archive\Unit\Mock;

use Drupal\web_page_archive\Plugin\CaptureUtility\HtmlCaptureUtility;

/**
 * Mock html capture utility.
 */
class MockAlwaysThrowingCaptureUtility extends HtmlCaptureUtility {

  /**
   * Constructor for mock html capture utility.
   */
  public function __construct() {
    parent::__construct([], 'AlwaysCaptureUtility', [
      'id' => 'AlwaysThrowingCaptureUtility',
      'label' => 'Always throwing capture utility',
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
  public function captureUrl($uri) {
    throw new \Exception('Oh no! I could not capture the URL.');
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    throw new \Exception('Oh no! I could not get the response.');
  }

}
