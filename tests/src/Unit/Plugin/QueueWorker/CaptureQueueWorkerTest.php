<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\QueueWorker;

use Drupal\Tests\UnitTestCase;
use Drupal\Tests\web_page_archive\Unit\Mock\MockAlwaysThrowingCaptureUtility;
use Drupal\Tests\web_page_archive\Unit\Mock\MockHtmlCaptureUtility;
use Drupal\Tests\web_page_archive\Unit\Mock\MockScreenshotCaptureUtility;
use Drupal\web_page_archive\Plugin\QueueWorker\CaptureQueueWorker;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\QueueWorker\CaptureQueueWorker
 *
 * @group web_page_archive
 */
class CaptureQueueWorkerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $definition = [
      'id' => 'web_page_archive_capture',
      'title' => 'title',
    ];
    $this->queue = new CaptureQueueWorker([], 'id', $definition);
  }

  /**
   * Tests a valid response.
   */
  public function testProcessItemReturnsResponse() {
    $data = [
      'utility' => new MockScreenshotCaptureUtility(),
      'url' => 'http://www.whatever.com',
    ];
    $response = $this->queue->processItem($data);
    $this->assertSame('uri', $response->getType());
    $this->assertSame('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg', $response->getContent());
  }

  /**
   * Tests missing utility writes message.
   *
   * @expectedException Exception
   * @expectedExceptionMessage utility is required
   */
  public function testMissingUtilityWritesMessage() {
    $data = [
      'url' => 'http://www.whatever.com',
    ];
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing url writes message.
   *
   * @expectedException Exception
   * @expectedExceptionMessage url is required
   */
  public function testMissingUrlWritesMessage() {
    $data = [
      'utility' => new MockHtmlCaptureUtility(),
    ];
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests failing capture utility.
   *
   * @expectedException Exception
   * @expectedExceptionMessage Oh no! I could not capture the URL.
   */
  public function testProcessItemWritesMessage() {
    $data = [
      'utility' => new MockAlwaysThrowingCaptureUtility(),
      'url' => 'http://www.whatever.com',
    ];
    $response = $this->queue->processItem($data);
  }

}

/**
 * Since drupal_set_message() is unavailable we need to cheat to get it in.
 *
 * @todo Delete after https://www.drupal.org/node/2278383 is in.
 * @see https://api.drupal.org/api/drupal/core!modules!aggregator!tests!src!Unit!Plugin!AggregatorPluginSettingsBaseTest.php/8.2.x
 */
namespace Drupal\web_page_archive\Plugin\QueueWorker;

if (!function_exists('drupal_set_message')) {

  /**
   * Stubs out drupal_set_message.
   */
  function drupal_set_message($message) {
    throw new \Exception($message);
  }

}
