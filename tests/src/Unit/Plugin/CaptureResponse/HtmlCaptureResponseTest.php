<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CaptureResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\wpa_html_capture\Plugin\CaptureResponse\HtmlCaptureResponse;

/**
 * @coversDefaultClass \Drupal\wpa_html_capture\Plugin\CaptureResponse\HtmlCaptureResponse
 *
 * @group web_page_archive
 */
class HtmlCaptureResponseTest extends UnitTestCase {

  /**
   * Test basic methods work as expected.
   */
  public function testBasicMethodsWorkCorrectly() {
    $response = new HtmlCaptureResponse('/some/path/to/file.html', 'http://www.somesite.com');

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

  /**
   * Test that HTML content is properly escaped.
   */
  public function testRenderedContentIsEscaped() {
    $file = tempnam('/tmp', 'wpa_');
    file_put_contents($file, '<p>Some HTML here</p>');
    $response = new HtmlCaptureResponse($file, 'https://www.whatever.com');
    $render_array = $response->renderable(['mode' => 'full']);
    $this->assertSame('<span class="html-tag">&lt;p&gt;</span>Some HTML here<span class="html-tag">&lt;/p&gt;</span>', $render_array['#markup']);
    unlink($file);
  }

}
