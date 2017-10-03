<?php

namespace Drupal\Tests\web_page_archive\Unit\Validator;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Validator\RobotsValidator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Validator\RobotsValidator
 *
 * @group web_page_archive
 */
class RobotsValidatorTest extends UnitTestCase {

  /**
   * Robots.txt validator.
   *
   * @var \Drupal\web_page_archive\Validator\RobotsValidator
   */
  protected static $robotsValidator;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/robots.good.txt')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/robots.empty.txt')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/robots.blocked_agent.txt')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/robots.invalid.txt')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/robots.invalid.png')),
      new Response(403, [], 'Access denied'),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    static::$robotsValidator = new RobotsValidator();
    static::$robotsValidator->initializeConnection($handler);
  }

  /**
   * Tests good URL is crawlable.
   */
  public function testsGoodUrlIsCrawlable() {
    $this->assertTrue(static::$robotsValidator->isCrawlable('http://www.goodsite.com/', 'WPA'));
  }

  /**
   * Tests bad URL is not crawlable.
   */
  public function testsBadUrlIsNotCrawlable() {
    $this->assertFalse(static::$robotsValidator->isCrawlable('http://www.goodsite.com/profiles/joe-smith', 'WPA'));
  }

  /**
   * Tests URL is crawlable in empty robots.txt file.
   */
  public function testsEmptyRobotsFileAllowsCrawling() {
    $this->assertTrue(static::$robotsValidator->isCrawlable('http://www.emptysite.com/profiles/joe-smith', 'WPA'));
  }

  /**
   * Tests blocked user agent failed.
   */
  public function testsBlockedUserAgentDisallowsCrawling() {
    $this->assertFalse(static::$robotsValidator->isCrawlable('https://www.blockedsite.com/about-us', 'WPA'));
  }

  /**
   * Tests unparseable file allows crawling.
   */
  public function testsUrlWithInvalidRobotsFileAllowsCrawling() {
    $this->assertTrue(static::$robotsValidator->isCrawlable('https://www.somesite.com/invalid.file.txt', 'WPA'));
  }

  /**
   * Tests unparseable file allows crawling.
   */
  public function testsUnparseableFileAllowsCrawling() {
    $this->assertTrue(static::$robotsValidator->isCrawlable('https://www.someothersite.com/invalid.file.png', 'WPA'));
  }

  /**
   * Tests 4xx client error allows crawling.
   */
  public function testsClientErrorAllowsCrawling() {
    $this->assertTrue(static::$robotsValidator->isCrawlable('https://www.yetanothersite.com/access-denied-page', 'WPA'));
  }

  /**
   * Tests connection failure allows crawling.
   */
  public function testsRequestErrorAllowsCrawling() {
    $this->assertTrue(static::$robotsValidator->isCrawlable('https://www.youcantconnecttome.com/peanut-butter', 'WPA'));
  }

}
