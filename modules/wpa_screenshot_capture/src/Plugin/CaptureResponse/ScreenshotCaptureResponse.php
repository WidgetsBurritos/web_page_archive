<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CaptureResponse;

use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;

/**
 * URI capture response.
 */
class ScreenshotCaptureResponse extends UriCaptureResponse {

  /**
   * {@inheritdoc}
   */
  public static function getId() {
    return 'wpa_screenshot_capture_response';
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
      $link_array['capture_url'] = ['#markup' => "<p class='wpa-captured-url'>{$url}</p>"];
    }

    // If capture has a screenshot show it, otherwise show error.
    if (!empty($this->content)) {
      $link_array['screenshot'] = [
        '#theme' => 'image_style',
        '#style_name' => 'web_page_archive_thumbnail',
        '#uri' => $this->content,
      ];
    }
    else {
      $link_array['screenshot'] = [
        '#markup' => $this->t('There was a problem generating this screenshot.'),
      ];
    }

    $render = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.web_page_archive.modal', $route_params),
      '#title' => $link_array,
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        // TODO: Pull this value from config?
        'data-dialog-options' => Json::encode(['width' => 1280]),
      ],
      '#attached' => ['library' => ['web_page_archive/admin']],
    ];

    return $render;
  }

  /**
   * Renders full mode.
   */
  private function renderFull(array $options) {
    // If capture has a screenshot show it, otherwise show error.
    if (!empty($this->content)) {
      return [
        '#theme' => 'image_style',
        '#style_name' => 'web_page_archive_full',
        '#uri' => $this->content,
        '#attached' => ['library' => ['web_page_archive/admin']],
      ];
    }

    return ['#markup' => $this->t('There was a problem generating this screenshot.')];
  }

  /**
   * {@inheritdoc}
   */
  public static function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $compare_utilities, array $tags = [], array $data = []) {
    $tags[] = 'screenshot';
    $tags[] = 'file';
    return parent::compare($a, $b, $compare_utilities, $tags, $data);
  }

}
