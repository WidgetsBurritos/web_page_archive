<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

use Drupal\Core\Url;
use Drupal\web_page_archive\Plugin\CompareResponseBase;

/**
 * The response that indicates the variance threshold for a response.
 */
class VarianceCompareResponse extends CompareResponseBase {

  use FileSizeTrait;
  use TextDiffTrait;

  protected $variance;

  /**
   * Creates a new VarianceCompareResponse object.
   */
  public function __construct($variance) {
    $this->variance = (float) $variance;
  }

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_variance_compare_response';
  }

  /**
   * Renders this response.
   */
  public function renderable(array $options = []) {
    // Otherwise render the proper mode.
    return (isset($options['mode']) && $options['mode'] == 'full') ?
      $this->renderFull($options) : $this->renderPreview($options);
  }

  /**
   * Retrieves the full preview Url from the options array.
   */
  protected function getFullModeUrlFromOptions(array $options) {
    $route_params = [
      'wpa_run_comparison' => $options['run_comparison']->id(),
      'index' => $options['index'],
    ];
    return Url::fromRoute('entity.wpa_run_comparison.modal', $route_params);
  }

  /**
   * Attaches the WPA library to the render array.
   */
  protected function attachLibrary(array $render = []) {
    $library = 'web_page_archive/admin';
    if (empty($render['#attached']['library']) || !in_array($library, $render['#attached']['library'])) {
      $render['#attached']['library'][] = $library;
    }
    return $render;
  }

  /**
   * Renders "preview" mode.
   */
  protected function renderPreview(array $options = []) {
    return [];
  }

  /**
   * Renders "full" mode.
   */
  protected function renderFull(array $options = []) {
    return [];
  }

}
