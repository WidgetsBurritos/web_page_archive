<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

/**
 * The response that indicates no variant was specified.
 */
class NoVariantCompareResponse extends VarianceCompareResponse {

  private $isLeft = FALSE;
  private $isRight = FALSE;

  /**
   * Constructs a new NoVariantCompareResponse object.
   *
   * Something vs nothing is always 100% different, so extending the
   * VarianceCompareResponse and setting the variance value to 100 makes sense.
   */
  public function __construct() {
    parent::__construct(100);
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_no_variant_compare_response';
  }

  /**
   * Indicates if the response is only on the left side of a comparison.
   *
   * @return bool
   *   A boolean indicated if response is on the left side of the comparison.
   */
  public function isLeft() {
    return $this->isLeft;
  }

  /**
   * Indicates if the response is only on the right side of a comparison.
   *
   * @return bool
   *   A boolean indicated if response is on the right side of the comparison.
   */
  public function isRight() {
    return $this->isRight;
  }

  /**
   * Marks as being a left only response.
   *
   * @return Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   Returns self.
   */
  public function markLeft() {
    $this->isLeft = TRUE;
    $this->isRight = FALSE;

    return $this;
  }

  /**
   * Marks as being a right only response.
   *
   * @return Drupal\web_page_archive\Plugin\CompareResponseInterface
   *   Returns self.
   */
  public function markRight() {
    $this->isLeft = FALSE;
    $this->isRight = TRUE;

    return $this;
  }

  /**
   * Renders this response.
   */
  public function renderable(array $options = []) {
    return ['#markup' => $this->t('Capture only occurs in one run.')];
  }

}
