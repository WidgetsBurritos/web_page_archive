<?php

namespace Drupal\web_page_archive\Plugin\ComparisonUtility;

use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\FilterableComparisonUtilityBase;
use Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse;

/**
 * Compares file size of two captures.
 *
 * @ComparisonUtility(
 *   id = "web_page_archive_file_size_compare",
 *   label = @Translation("File: Size", context = "Web Page Archive"),
 *   description = @Translation("Compares images based on file size.", context = "Web Page Archive"),
 *   tags = {"file"}
 * )
 */
class FileSizeComparisonUtility extends FilterableComparisonUtilityBase {

  /**
   * {@inheritdoc}
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $data = []) {
    $size1 = $a->getCaptureSize();
    $size2 = $b->getCaptureSize();
    $variance = 100 * abs($size2 - $size1) / $size1;

    if ($variance === 0) {
      return $this->compareResponseFactory->getSameCompareResponse();
    }

    $response = new FileSizeVarianceCompareResponse($variance);
    $response->setFile1Size($size1);
    $response->setFile2Size($size2);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilterCriteria() {
    return [
      FileSizeVarianceCompareResponse::getId() => $this->label(),
    ];
  }

}
