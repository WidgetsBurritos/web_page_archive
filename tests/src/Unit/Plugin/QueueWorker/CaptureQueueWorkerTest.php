<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\QueueWorker;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;
use Drupal\web_page_archive\Plugin\QueueWorker\CaptureQueueWorker;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\QueueWorker\CaptureQueueWorker
 *
 * @group web_page_archive
 */
class CaptureQueueWorkerTest extends UnitTestCase {

  /**
   * Capture queue.
   *
   * @var \Drupal\web_page_archive\Plugin\QueueWorker\CaptureQueueWorker
   */
  protected $queue;

  /**
   * Mock web page archive run.
   *
   * @var \Drupal\web_page_archive\Entity\WebPageArchiveRun
   */
  protected $mockWebPageArchiveRun;

  /**
   * Mock screenshot capture utility.
   *
   * @var \Drupal\web_page_archive\Plugin\ScreenshotCaptureUtility
   */
  protected $mockScreenshotCaptureUtility;

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

    $this->mockWebPageArchiveRun = $this->getMockBuilder('\Drupal\web_page_archive\Entity\WebPageArchiveRun')
      ->disableOriginalConstructor()
      ->setMethods(['markCaptureComplete'])
      ->getMock();

    $this->mockScreenshotCaptureUtility = $this->getMockBuilder('\Drupal\web_page_archive\Plugin\CaptureUtility\ScreenshotCaptureUtility')
      ->disableOriginalConstructor()
      ->getMock();
    $this->mockScreenshotCaptureUtility->expects($this->any())
      ->method('capture')
      ->will($this->returnSelf());
    $this->mockScreenshotCaptureUtility
      ->method('getResponse')
      ->will($this->returnValue(new UriCaptureResponse('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg')));
  }

  /**
   * Tests a valid response.
   */
  public function testProcessItemReturnsResponse() {
    $data = [
      'utility' => $this->mockScreenshotCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
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
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
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
      'utility' => $this->mockScreenshotCaptureUtility,
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
    ];
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing run_uuid writes message.
   *
   * @expectedException Exception
   * @expectedExceptionMessage run_uuid is required
   */
  public function testMissingRunUuidWritesMessage() {
    $data = [
      'utility' => $this->mockScreenshotCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_entity' => $this->mockWebPageArchiveRun,
    ];
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing run_entity writes message.
   *
   * @expectedException Exception
   * @expectedExceptionMessage run_entity is required
   */
  public function testMissingRunEntityWritesMessage() {
    $data = [
      'utility' => $this->mockScreenshotCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
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
    $failing_utility = $this->getMockBuilder('\Drupal\web_page_archive\Plugin\CaptureUtility\HtmlCaptureUtility')
      ->disableOriginalConstructor()
      ->getMock();
    $failing_utility->expects($this->any())
      ->method('capture')
      ->will($this->throwException(new \Exception('Oh no! I could not capture the URL.')));

    $data = [
      'utility' => $failing_utility,
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
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
