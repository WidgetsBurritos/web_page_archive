<?php

namespace Drupal\wpa_html_capture\Plugin\CompareResponse;

use Drupal\Component\Serialization\Json;
use Drupal\web_page_archive\Plugin\CompareResponse\VarianceCompareResponse;

/**
 * The response that indicates the variance threshold for a HTML response.
 */
class HtmlVarianceCompareResponse extends VarianceCompareResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_html_variance_compare_response';
  }

  /**
   * {@inheritdoc}
   */
  public function renderPreview(array $options = []) {
    $render = [];

    $render['variance'] = [
      '#prefix' => '<div class="wpa-comparison-variance">',
      '#markup' => $this->t('HTML Line Variance: @variance%', ['@variance' => $this->getVariance()]),
      '#suffix' => '</div>',
    ];

    $render['link'] = [
      '#type' => 'link',
      '#url' => $this->getFullModeUrlFromOptions($options),
      '#title' => $this->t('View HTML Diff'),
      '#attributes' => [
        'class' => ['use-ajax', 'button', 'button--small', 'button--primary'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 1280]),
      ],
    ];

    return $this->attachLibrary($render);
  }

  /**
   * {@inheritdoc}
   */
  public function renderFull(array $options = []) {
    // If diff has not be sent, just show percentage summary.
    if (empty($this->getDiff())) {
      return ['#markup' => $this->t('There is a @variance% difference between the two captures.', ['@variance' => $this->variance])];
    }
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
