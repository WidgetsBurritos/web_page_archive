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
  private function getDiffOp($type, $orig_ct, $closing_ct) {
    $diff_op = new DiffOp();
    $diff_op->type = $type;
    $diff_op->orig = array_fill(0, $orig_ct, 'x');
    $diff_op->closing = array_fill(0, $closing_ct, 'y');
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
      $this->getDiffOp('add', 3, 7),
      $this->getDiffOp('copy', 3, 3),
      $this->getDiffOp('change', 7, 3),
      $this->getDiffOp('delete', 3, 1),
      $this->getDiffOp('empty', 0, 0),
      $this->getDiffOp('copy-and-change', 6, 12),
      $this->getDiffOp('copy-change-copy', 1, 3),
      $this->getDiffOp('copy-change-copy-add', 3, 4),
      $this->getDiffOp('copy-delete', 3, 7),
      $this->getDiffOp('invalid-does-not-exist', 3, 13),
    ];
    $this->assertEquals(94, ceil(UriCaptureResponse::calculateDiffVariance($diff_edits)));
  }

}
