<?php

namespace Drupal\Tests\web_page_archive\Kernel\Controller;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;

/**
 * Tests the functionality of the web page archive controller.
 *
 * @group web_page_archive
 */
class WebPageArchiveControllerTest extends EntityStorageTestBase {

  /**
   * Tests WebPageArchiveController batch functionality.
   */
  public function testBatchFunctionality() {
    $urls = [
      'http://localhost',
      'http://localhost/some-other-page',
      'http://localhost/yet-another-page',
    ];
    $web_page_archive = $this->getWpaEntity('My run entity', $urls);
    $web_page_archive->startNewRun();
    $context = [];

    // Confirm queue size matches URL list.
    $queue = $web_page_archive->getQueue();
    $this->assertEquals(3, $queue->numberOfItems());

    // Attempt to process the next item in the queue.
    $this->webPageArchiveController->batchProcess($web_page_archive, $context);
    $this->assertEquals(2, $queue->numberOfItems());
    $this->assertEquals($web_page_archive, $context['results']['entity']);

    // Attempt to process the next item in the queue.
    $this->webPageArchiveController->batchProcess($web_page_archive, $context);
    $this->assertEquals(1, $queue->numberOfItems());
    $this->assertEquals($web_page_archive, $context['results']['entity']);

    // Attempt to process the next item in the queue.
    $this->webPageArchiveController->batchProcess($web_page_archive, $context);
    $this->assertEquals(0, $queue->numberOfItems());
    $this->assertEquals($web_page_archive, $context['results']['entity']);

    // Simulate "batch finish" process.
    $this->webPageArchiveController->batchFinished(TRUE, $context['results'], []);
    $expected = ['status' => ['The capture has been completed.']];
    $this->assertEquals($expected, \Drupal::messenger()->all());

  }

}
