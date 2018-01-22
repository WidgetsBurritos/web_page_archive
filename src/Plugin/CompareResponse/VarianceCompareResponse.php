<?php

namespace Drupal\web_page_archive\Plugin\CompareResponse;

use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
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
    // If diff has not be sent, just show percentage summary.
    if (empty($this->getDiff())) {
      return ['#markup' => $this->t('There is a @variance% difference between the two captures.', ['@variance' => $this->variance])];
    }
    // Otherwise render the proper mode.
    return (isset($options['mode']) && $options['mode'] == 'full') ?
      $this->renderFull($options) : $this->renderPreview($options);
  }

  /**
   * Renders "preview" mode.
   */
  protected function renderPreview(array $options) {
    $render = [];
    $route_params = [
      'wpa_run_comparison' => $options['run_comparison']->id(),
      'index' => $options['index'],
    ];

    $ct = 0;
    foreach ($options['runs'] as $details) {
      $ct++;
      $replacements = [
        '@number' => $ct,
        '@size' => format_size($details[$options["delta{$ct}"]]['capture_size']),
      ];
      $render["size{$ct}"] = [
        '#prefix' => '<div class="wpa-comparison-file-size">',
        '#markup' => $this->t('Size @number: @size', $replacements),
        '#suffix' => '</div>',
      ];
    }

    $render['link'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.wpa_run_comparison.modal', $route_params),
      '#title' => $this->t('Display'),
      '#attributes' => [
        'class' => ['use-ajax', 'button', 'button--small', 'button--primary'],
        'data-dialog-type' => 'modal',
        // TODO: Pull this value from config?
        'data-dialog-options' => Json::encode(['width' => 1280]),
      ],
    ];

    $render['#attached'] = ['library' => ['web_page_archive/admin']];

    return $render;
  }

  /**
   * Renders "full" mode.
   */
  protected function renderFull(array $options = []) {
    $diff_formatter = \Drupal::service('diff.formatter');
    $diff_formatter->show_header = FALSE;
    $build = [
      '#attached' => ['library' => ['web_page_archive/diff']],
      'diff' => [
        '#type' => 'table',
        '#attributes' => ['class' => ['wpa-diff']],
        '#header' => [
          ['data' => $this->t('Run #1'), 'colspan' => '2'],
          ['data' => $this->t('Run #2'), 'colspan' => '2'],
        ],
        '#rows' => $diff_formatter->format($this->getDiff()),
      ],
    ];
    return $build;
  }

}
