<?php

namespace Drupal\web_page_archive\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Filters URLs based on whether or not they are crawlable.
 */
class RobotsValidator {

  /**
   * Http client.
   *
   * @var GuzzleHttp\Client
   */
  private $httpClient;

  /**
   * Initializes an http client for fetching robots.txt files.
   *
   * @param \GuzzleHttp\HandlerStack $handler
   *   Non-default http client handler.
   */
  public function initializeConnection(HandlerStack $handler = NULL) {
    $client_options = [];
    if (isset($handler)) {
      $client_options['handler'] = $handler;
    }
    $this->httpClient = new Client($client_options);

    return $this;
  }

  /**
   * Determines if a URL is crawlable based on robots.txt file.
   */
  public function isCrawlable($url, $user_agent = 'WPA') {
    static $robots = [];

    // Use default client if not previously set.
    if (!isset($this->httpClient)) {
      $this->initializeConnection();
    }

    // Confirm we're dealing with a valid URL.
    $url_tokens = parse_url($url);
    if (empty($url_tokens['scheme']) || empty($url_tokens['host'])) {
      throw new \Exception('Invalid URL specified');
    }

    // Assemble robots.txt path.
    $port_string = (!empty($url_tokens['port'])) ? ":{$url_tokens['port']}" : '';
    $robots_file = "{$url_tokens['scheme']}://{$url_tokens['host']}{$port_string}/robots.txt";

    // Statically store response for the robots.txt file. This prevents a bunch
    // of repetitive calls to the same robots.txt file during a single
    // connection.
    if (!isset($robots[$robots_file])) {
      try {
        $robots[$robots_file] = (string) $this->httpClient->request('GET', $robots_file)->getBody();
      }
      catch (\Exception $e) {
        $robots[$robots_file] = -1;
      }
    }

    // While this might seem counter intuitive, if an exception is thrown above,
    // this simply means that there were no valid robots.txt restrictions here.
    // Thus from the perspective of this particular service this URL is
    // considered to be crawlable. We will rely on external validation to
    // verify URLs are valid.
    if ($robots[$robots_file] === -1) {
      return TRUE;
    }

    // Apply robots.txt to URL.
    $parser = new \RobotsTxtParser($robots[$robots_file]);
    return $parser->isAllowed($url, $user_agent);
  }

  /**
   * Removes all uncrawlable urls from an array of urls.
   */
  public function filterUrls(array $urls = []) {
    foreach ($urls as $url) {
      if ($this->isCrawlable($url)) {
        yield $url;
      }
    }
  }

}
