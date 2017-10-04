<?php

namespace Drupal\wpa_html_capture\Plugin\CaptureResponse;

use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
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
        'data-dialog-options' => Json::encode(['width' => 1024]),
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
   * Renders full mode.
   */
  private function renderFull(array $options) {
    // If capture has a screenshot show it, otherwise show error.
    if (!empty($this->content)) {
      $file = file_get_contents($this->content);
      $highlighter = new Highlighter(new OutputHtml());
      $highlighter->setLexer(new LexerHtml());

      return [
        '#prefix' => '<pre class="wpa-code-window">',
        '#markup' => $highlighter->highlight($file),
        '#suffix' => '</pre>',
        '#attached' => ['library' => ['web_page_archive/fshl']],
      ];
    }

    return ['#markup' => $this->t('There was a problem generating this capture.')];
  }

}
