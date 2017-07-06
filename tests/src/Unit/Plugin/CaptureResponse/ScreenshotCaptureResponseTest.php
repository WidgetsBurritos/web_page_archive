<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CaptureResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse
 *
 * @group web_page_archive
 */
class ScreenshotCaptureResponseTest extends UnitTestCase {

  /**
   * Test runs()
   */
  public function testConstructor() {
    $response = new ScreenshotCaptureResponse('<p>My response content</p>');

    // Test getters.
    $this->assertSame('uri', $response->getType());
    $this->assertSame('<p>My response content</p>', $response->getContent());

    // Test serialized output.
    $expected_serialized = serialize([
      'type' => 'uri',
      'content' => '<p>My response content</p>',
    ]);
    $this->assertSame($expected_serialized, $response->getSerialized());
  }

}
