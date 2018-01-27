<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

use Drupal\web_page_archive\Plugin\CompareResponseBase;
use Drupal\web_page_archive\Plugin\CompareResponseInterface;

/**
 * A compare response that is simply a collection of other responses.
 */
class CompareResponseCollection extends CompareResponseBase {

  protected $responses;

  /**
   * Constructs a new CompareResponseCollection object.
   */
  public function __construct() {
    $this->responses = [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_multiple_compare_response';
  }

  /**
   * Adds a response to the list.
   */
  public function addResponse(CompareResponseInterface $compare_response, array $options = []) {
    $this->responses[] = $compare_response;
  }

  /**
   * Retrieves full list of responses.
   */
  public function getResponses() {
    return $this->responses;
  }

  /**
   * Renders this response.
   */
  public function renderable(array $options = []) {
    $build = [];
    $idx = 0;
    foreach ($this->responses as $response) {
      $build[$idx++] = $response->renderable($options);
    }

    return $build;
  }

}
