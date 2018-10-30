<?php

namespace Drupal\wpa_html_capture\Plugin\ComparisonUtility;

use Drupal\Component\Diff\Diff;
use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\FilterableComparisonUtilityBase;
use Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse;
use Drupal\web_page_archive\Plugin\CompareResponse\TextDiffTrait;

/**
 * Captures screenshot of a remote uri.
 *
 * @ComparisonUtility(
 *   id = "wpa_html_diff_compare",
 *   label = @Translation("HTML: Diff", context = "Web Page Archive"),
 *   description = @Translation("Compares HTML line-by-line.", context = "Web Page Archive"),
 *   tags = {"html"}
 * )
 */
class HtmlDiffComparisonUtility extends FilterableComparisonUtilityBase {

  use TextDiffTrait;

  /**
   * {@inheritdoc}
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $data = []) {
    $response_factory = \Drupal::service('web_page_archive.compare.response');
    $a_content = explode(PHP_EOL, $a->retrieveFileContents());
    $b_content = explode(PHP_EOL, $b->retrieveFileContents());
    $diff = new Diff($a_content, $b_content);
    if ($diff->isEmpty()) {
      return $response_factory->getSameCompareResponse();
    }
    $variance = static::calculateDiffVariance($diff->getEdits());
    $response = new HtmlVarianceCompareResponse($variance);
    $response->setDiff($diff);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilterCriteria() {
    return [
      HtmlVarianceCompareResponse::getId() => $this->label(),
    ];
  }

}
