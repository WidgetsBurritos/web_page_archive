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
   * Mock HTML capture utility.
   *
   * @var \Drupal\wpa_html_capture\Plugin\HtmlCaptureUtility
   */
  protected $mockHtmlCaptureUtility;

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

    $this->mockHtmlCaptureUtility = $this->getMockBuilder('\Drupal\wpa_html_capture\Plugin\CaptureUtility\HtmlCaptureUtility')
      ->disableOriginalConstructor()
      ->getMock();
    $this->mockHtmlCaptureUtility->expects($this->any())
      ->method('capture')
      ->will($this->returnSelf());
    $this->mockHtmlCaptureUtility
      ->method('getResponse')
      ->will($this->returnValue(new UriCaptureResponse('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg', 'http://www.somesite.com')));
  }

  /**
   * Tests a valid response.
   */
  public function testProcessItemReturnsResponse() {
    $data = [
      'utility' => $this->mockHtmlCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
      'user_agent' => 'WPA',
    ];
    $response = $this->queue->processItem($data);
    $this->assertSame('uri', $response->getType());
    $this->assertSame('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg', $response->getContent());
  }

  /**
   * Tests missing utility writes message.
   */
  public function testMissingUtilityWritesMessage() {
    $data = [
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
      'user_agent' => 'WPA',
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('utility is required');
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing url writes message.
   */
  public function testMissingUrlWritesMessage() {
    $data = [
      'utility' => $this->mockHtmlCaptureUtility,
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
      'user_agent' => 'WPA',
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('url is required');
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing run_uuid writes message.
   */
  public function testMissingRunUuidWritesMessage() {
    $data = [
      'utility' => $this->mockHtmlCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_entity' => $this->mockWebPageArchiveRun,
      'user_agent' => 'WPA',
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('run_uuid is required');
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing run_entity writes message.
   */
  public function testMissingRunEntityWritesMessage() {
    $data = [
      'utility' => $this->mockHtmlCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'user_agent' => 'WPA',
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('run_entity is required');
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests missing user_agent writes message.
   */
  public function testMissingUserAgentWritesMessage() {
    $data = [
      'utility' => $this->mockHtmlCaptureUtility,
      'url' => 'http://www.whatever.com',
      'run_uuid' => '12345678-1234-1234-1234-123456789000',
      'run_entity' => $this->mockWebPageArchiveRun,
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('user_agent is required');
    $response = $this->queue->processItem($data);
  }

  /**
   * Tests failing capture utility.
   */
  public function testProcessItemWritesMessage() {
    $failing_utility = $this->getMockBuilder('\Drupal\wpa_html_capture\Plugin\CaptureUtility\HtmlCaptureUtility')
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
      'user_agent' => 'WPA',
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Oh no! I could not capture the URL.');
    $response = $this->queue->processItem($data);
  }

}
