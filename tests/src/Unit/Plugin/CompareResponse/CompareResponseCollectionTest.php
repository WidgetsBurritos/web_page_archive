<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\CompareResponse;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection;
use Drupal\web_page_archive\Plugin\CompareResponse\EmptyCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection
 *
 * @group web_page_archive
 */
class CompareResponseCollectionTest extends UnitTestCase {

  /**
   * Tests that responses are added to the collection.
   */
  public function testResponsesAreAddedToCollection() {
    $response_collection = new CompareResponseCollection();
    $response1 = new EmptyCompareResponse();
    $response2 = new SameCompareResponse();
    $response_collection->addResponse($response1);
    $response_collection->addResponse($response2);
    $expected = [$response1, $response2];
    $this->assertEquals($expected, $response_collection->getResponses());
  }

}
