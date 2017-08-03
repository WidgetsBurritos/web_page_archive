<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CaptureResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse
 *
 * @group web_page_archive
 */
class UriCaptureResponseTest extends UnitTestCase {

  /**
   * Test runs()
   */
  public function testConstructor() {
    $response = new UriCaptureResponse('/some/path/to/file.html', 'http://www.somesite.com');

    // Test getters.
    $this->assertSame('uri', $response->getType());
    $this->assertSame('/some/path/to/file.html', $response->getContent());

    // Test serialized output.
    $expected_serialized = serialize([
      'type' => 'uri',
      'content' => '/some/path/to/file.html',
    ]);
    $this->assertSame($expected_serialized, $response->getSerialized());
  }

}
