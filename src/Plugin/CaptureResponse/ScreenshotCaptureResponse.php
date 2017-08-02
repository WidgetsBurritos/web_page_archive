<?php

namespace Drupal\web_page_archive\Plugin\CaptureResponse;

/**
 * URI capture response.
 */
class ScreenshotCaptureResponse extends UriCaptureResponse {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $render = [
      'capture_url' => [
        '#type' => 'link',
        '#url' => $this->captureUrl,
        '#title' => $this->captureUrl,
      ],
      'screenshot' => [
        '#theme' => 'image_style',
        '#style_name' => 'web_page_archive_thumbnail',
        '#uri' => $this->content,
      ]
    ];

    return render($render);
  }

}
