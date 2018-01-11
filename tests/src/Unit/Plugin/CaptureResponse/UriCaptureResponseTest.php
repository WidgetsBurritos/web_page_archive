<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CaptureResponse;

use Drupal\Component\Diff\Engine\DiffOp;
use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse
 *
 * @group web_page_archive
 */
class UriCaptureResponseTest extends UnitTestCase {

  /**
   * Helper method to retrieve a DiffOp operation.
   */
  private function getDiffOp($type) {
    $diff_op = new DiffOp();
    $diff_op->type = $type;
    return $diff_op;
  }

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

  /**
   * Test that the variance calculates properly based on ops.
   */
  public function testCalculateDiffVariance() {
    $diff_edits = [
      $this->getDiffOp('add'),
      $this->getDiffOp('copy'),
      $this->getDiffOp('change'),
      $this->getDiffOp('delete'),
      $this->getDiffOp('empty'),
      $this->getDiffOp('copy-and-change'),
      $this->getDiffOp('copy-change-copy'),
      $this->getDiffOp('copy-change-copy-add'),
      $this->getDiffOp('copy-delete'),
      $this->getDiffOp('invalid-does-not-exist'),
    ];
    $this->assertEquals(78, ceil(UriCaptureResponse::calculateDiffVariance($diff_edits)));
  }

}
