<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\web_page_archive\Plugin\CompareResponse\EmptyCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection;
use Drupal\web_page_archive\Plugin\CompareResponse\NoVariantCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse;

/**
 * Service that returns compare response objects.
 */
class CompareResponseFactory {

  /**
   * Retrieves an empty compare response.
   *
   * @return \Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   An EmptyCompareResponse object.
   */
  public function getEmptyCompareResponse() {
    return new EmptyCompareResponse();
  }

  /**
   * Retrieves no variant compare response.
   *
   * @return \Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   A NoVariantCompareResponse object.
   */
  public function getNoVariantCompareResponse() {
    return new NoVariantCompareResponse();
  }

  /**
   * Retrieves an same compare response.
   *
   * @return \Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   An SameCompareResponse object.
   */
  public function getSameCompareResponse() {
    return new SameCompareResponse();
  }

  /**
   * Retrieves a variance compare response.
   *
   * @return \Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   An VarianceCompareResponse object.
   */
  public function getVarianceCompareResponse($variance) {
    return new VarianceCompareResponse($variance);
  }

  /**
   * Retrieves a multiple compare response.
   *
   * @return \Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   A CompareResponseCollection object.
   */
  public function getCompareResponseCollection() {
    return new CompareResponseCollection();
  }

}
