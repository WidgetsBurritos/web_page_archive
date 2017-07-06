<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CaptureResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CaptureResponse\HtmlCaptureResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CaptureResponse\HtmlCaptureResponse
 *
 * @group web_page_archive
 */
class HtmlCaptureResponseTest extends UnitTestCase {

  /**
   * Test runs()
   */
  public function testConstructor() {
    $response = new HtmlCaptureResponse('<p>My response content</p>');

    // Test getters.
    $this->assertSame(HtmlCaptureResponse::TYPE_HTML, $response->getType());
    $this->assertSame('<p>My response content</p>', $response->getContent());

    // Test serialized output.
    $expected_serialized = serialize([
      'type' => HtmlCaptureResponse::TYPE_HTML,
      'content' => '<p>My response content</p>',
    ]);
    $this->assertSame($expected_serialized, $response->getSerialized());
  }

}
