<?php

namespace Drupal\Tests\web_page_archive\Unit\Mock;

use Drupal\web_page_archive\Entity\WebPageArchive;

/**
 * Mock screenshot capture utility.
 */
class MockWebPageArchive extends WebPageArchive {

  /**
   * Constructor for mock web page archive.
   */
  public function __construct() {
    $values = [
      'id' => 'mock_archive',
      'label' => 'Mock Archive',
      'sitemap_url' => 'http://www.somesite.com/sitemap.xml',
      'cron_schedule' => '',
      'capture_html' => TRUE,
      'capture_screenshot' => TRUE,
      'capture_utilities' => [
        '00000000-1111-2222-3333-444455556666' => [
          'id' => 'HtmlCaptureUtility',
          'uuid' => '00000000-1111-2222-3333-444455556666',
        ],
        '99999999-8888-7777-6666-555544443333' => [
          'id' => 'ScreenshotCaptureUtility',
          'uuid' => '99999999-8888-7777-6666-555544443333',
        ],
      ],
    ];
    parent::__construct($values, 'web_page_archive');
  }

  /**
   * {@inheritdoc}
   */
  protected function storeNewRun($uuid, $queue_ct) {
  }

  /**
   * {@inheritdoc}
   */
  public function markCaptureComplete($data) {
  }

}
