<?php

namespace Drupal\Tests\web_page_archive\Unit\Parser;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Parser\SitemapParser;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Parser\SitemapParser
 *
 * @group web_page_archive
 */
class SitemapParserTest extends UnitTestCase {

  /**
   * Sitemap parser.
   *
   * @var \Drupal\web_page_archive\Parser\SitemapParser
   */
  protected static $sitemapParser;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    // Setup our Mock Connection.
    $mock = new MockHandler([
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/sitemap.good.xml')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/sitemap.empty.xml')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/invalid.file.html')),
      new Response(200, [], file_get_contents(__DIR__ . '/fixtures/invalid.file.png')),
      new Response(403, [], 'Access denied'),
      new RequestException("Error Communicating with Server", new Request('GET', 'test')),
    ]);
    $handler = HandlerStack::create($mock);
    static::$sitemapParser = new SitemapParser();
    static::$sitemapParser->initializeConnection($handler);
  }

  /**
   * Tests good sitemap.
   */
  public function testParsesGoodSitemapCorrectly() {
    // Good sitemap.
    $urls = static::$sitemapParser->parse('https://www.somesite.com/sitemap.good.xml');
    $expected = [
      'https://www.somesite.com/',
      'https://www.somesite.com/about/',
    ];
    $this->assertSame($expected, $urls);
  }

  /**
   * Tests empty sitemap.
   */
  public function testsParsesEmptySitemapCorrectly() {
    // Empty sitemap.
    $urls = static::$sitemapParser->parse('https://www.someemptysite.com/sitemap.empty.xml');
    $this->assertSame([], $urls);

  }

  /**
   * Tests parseable, but invalid file format (e.g. html).
   */
  public function testsParsesNonSitemapCorrectly() {
    $urls = static::$sitemapParser->parse('https://www.somesite.com/invalid.file.html');
    $this->assertSame([], $urls);
  }

  /**
   * Tests unparseable file throws exception.
   *
   * @expectedException Symfony\Component\Serializer\Exception\UnexpectedValueException
   */
  public function testsUnparseableFileThrowsException() {
    $urls = static::$sitemapParser->parse('https://www.somesite.com/invalid.file.png');
  }

  /**
   * Tests 4xx client error throws exception.
   *
   * @expectedException GuzzleHttp\Exception\ClientException
   */
  public function testsClientErrorThrowsException() {
    $urls = static::$sitemapParser->parse('https://www.somesite.com/access-denied-page');
  }

  /**
   * Tests connection failures.
   *
   * @expectedException GuzzleHttp\Exception\RequestException
   */
  public function testsRequestErrorThrowsException() {
    $urls = static::$sitemapParser->parse('https://www.youcantconnecttome.com/sitemap.xml');
  }

}
