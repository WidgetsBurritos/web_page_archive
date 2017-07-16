<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\web_page_archive\Entity\WebPageArchive;
use Drupal\web_page_archive\Plugin\CaptureResponse\HtmlCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtilityBase;

/**
 * Captures HTML of a remote uri.
 *
 * @CaptureUtility(
 *   id = "HtmlCaptureUtility",
 *   label = @Translation("Html capture utility", context = "Web Page Archive"),
 * )
 */
class HtmlCaptureUtility extends CaptureUtilityBase {

  /**
   * Most recent response.
   *
   * @var string|null
   */
  private $response = NULL;

  /**
   * {@inheritdoc}
   */
  public function capture(array $data = []) {
    // TODO: Do the actual capture.
    $this->response = new HtmlCaptureResponse('<p>Simulated response</p>');

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * {@inheritdoc}
   */
  public function addConfigFormFields(array $form, WebPageArchive $web_page_archive = NULL) {
    // Default form options:
    $config = [
      "{$this->pluginId}" => FALSE,
    ];

    // Look for set values.
    if (isset($web_page_archive)) {
      $instance = $web_page_archive->hasCaptureUtilityInstance($this->pluginId);
      $config = $instance['config'];
    }
    $form[$this->pluginId] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Capture HTML?'),
      '#description' => $this->t('If checked, this job will include download and compare HTML.'),
      // '#default_value' => $web_page_archive->isScreenshotCapturing(),
      '#default_value' => $config[$this->pluginId],
    ];

    return $form;
  }

}
