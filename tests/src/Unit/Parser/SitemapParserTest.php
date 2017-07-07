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
   * Tests sitemap parsing.
   */
  public function testSitemapParser() {
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
    $sitemap_parser = new SitemapParser($handler);

    // Good sitemap.
    $urls = $sitemap_parser->parse('https://www.somesite.com/sitemap.good.xml');
    $expected = [
      'https://www.somesite.com/',
      'https://www.somesite.com/about/',
    ];
    $this->assertSame($expected, $urls);

    // Empty sitemap.
    $urls = $sitemap_parser->parse('https://www.someemptysite.com/sitemap.empty.xml');
    $this->assertSame([], $urls);

    // Bad format (parseable).
    $urls = $sitemap_parser->parse('https://www.somesite.com/invalid.file.html');
    $this->assertSame([], $urls);

    // Bad format (unparseable).
    $this->setExpectedException('\Symfony\Component\Serializer\Exception\UnexpectedValueException');
    $urls = $sitemap_parser->parse('https://www.somesite.com/invalid.file.png');

    // Access denied.
    $this->setExpectedException('\GuzzleHttp\Exception\ClientException');
    $urls = $sitemap_parser->parse('https://www.somesite.com/access-denied-page');

    // Connection failure.
    $this->setExpectedException('\GuzzleHttp\Exception\RequestException');
    $urls = $sitemap_parser->parse('https://www.youcantconnecttome.com/sitemap.xml');

  }

}
