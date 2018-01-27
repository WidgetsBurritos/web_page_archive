<?php

namespace Drupal\wpa_html_capture\Plugin\CaptureResponse;

use Drupal\Core\Url;
use Drupal\Component\Diff\Diff;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;
use FSHL\Highlighter;
use FSHL\Output\Html as OutputHtml;
use FSHL\Lexer\Html as LexerHtml;

/**
 * URI capture response.
 */
class HtmlCaptureResponse extends UriCaptureResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_html_capture_response';
  }

  /**
   * {@inheritdoc}
   */
  public function renderable(array $options = []) {
    return (isset($options['mode']) && $options['mode'] == 'full') ?
      $this->renderFull($options) : $this->renderPreview($options);
  }

  /**
   * Renders "preview" mode.
   */
  private function renderPreview(array $options) {
    $link_array = [];

    $route_params = [
      'web_page_archive_run_revision' => $options['vid'],
      'delta' => $options['delta'],
    ];

    // If capture has a URL show it.
    if (!empty($this->captureUrl)) {
      $url = Html::escape($this->captureUrl);
      $link_array['capture_url'] = ['#markup' => "<p>{$url}</p>"];
    }

    $render['link'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.web_page_archive.modal', $route_params),
      '#title' => $link_array,
      '#markup' => 'body',
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        // TODO: Pull this value from config?
        'data-dialog-options' => Json::encode(['width' => 1280]),
      ],
    ];

    $render['path'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => [
        'class' => ['wpa-hidden', 'wpa-file-path'],
      ],
      '#value' => $this->content,
    ];

    $render['#attached'] = ['library' => ['web_page_archive/admin']];

    return $render;
  }

  /**
   * Retrieves file contents.
   */
  public function retrieveFileContents() {
    if (!empty($this->content) && file_exists($this->content)) {
      return trim(file_get_contents($this->content));
    }
    return '';
  }

  /**
   * Renders full mode.
   */
  private function renderFull(array $options) {
    // If capture has a screenshot show it, otherwise show error.
    $contents = $this->retrieveFileContents();
    if (!empty($contents)) {
      $highlighter = new Highlighter(new OutputHtml());
      $highlighter->setLexer(new LexerHtml());

      return [
        '#prefix' => '<pre class="wpa-code-window">',
        '#markup' => $highlighter->highlight($contents),
        '#suffix' => '</pre>',
        '#attached' => ['library' => ['web_page_archive/fshl']],
      ];
    }

    return ['#markup' => $this->t('There was a problem generating this capture.')];
  }

  /**
   * {@inheritdoc}
   */
  public static function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $capture_utilities) {
    $response_factory = \Drupal::service('web_page_archive.compare.response');
    $a_content = explode(PHP_EOL, $a->retrieveFileContents());
    $b_content = explode(PHP_EOL, $b->retrieveFileContents());
    $diff = new Diff($a_content, $b_content);
    if ($diff->isEmpty()) {
      return $response_factory->getSameCompareResponse();
    }
    $variance = static::calculateDiffVariance($diff->getEdits());
    $response = $response_factory->getVarianceCompareResponse($variance);
    $response->setDiff($diff);
    return $response;
  }

}
