<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Render\Markup;

/**
 * Captures HTML of a remote uri.
 *
 * @CaptureUtility(
 *   id = "HtmlCapture",
 *   label = @Translation("Html capture utility", context = "Web Page Archive"),
 * )
 */
class HtmlCaptureUtility extends CaptureUtilityBase {

  /**
   * Most recent response.
   *
   * @var string|NULL
   */
  private $response = NULL;

  /**
   * {@inheritdoc}
   */
  public function captureUrl($uri) {
    // TODO: Do something.
    $this->response = Markup::create('<p>Some Markup');

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

}
