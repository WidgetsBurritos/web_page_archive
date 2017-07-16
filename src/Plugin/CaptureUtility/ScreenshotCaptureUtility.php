<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\web_page_archive\Entity\WebPageArchive;
use Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtilityBase;

/**
 * Captures screenshot of a remote uri.
 *
 * @CaptureUtility(
 *   id = "ScreenshotCaptureUtility",
 *   label = @Translation("Screenshot capture utility", context = "Web Page Archive"),
 * )
 */
class ScreenshotCaptureUtility extends CaptureUtilityBase {

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
    $this->response = new ScreenshotCaptureResponse('https://upload.wikimedia.org/wikipedia/commons/c/c1/Drupal-wordmark.svg');

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
      "{$this->pluginId}_width" => 1280,
    ];

    // Look for set values.
    if (isset($web_page_archive)) {
      $instance = $web_page_archive->hasCaptureUtilityInstance($this->pluginId);
      $config = $instance['config'];
    }

    // Setup form fields.
    $form[$this->pluginId] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Capture Screenshot?'),
      '#description' => $this->t('If checked, this job will include download and compare screenshots.'),
      // '#default_value' => $web_page_archive->isScreenshotCapturing(),
      '#default_value' => $config[$this->pluginId],
    ];
    $form["{$this->pluginId}_width"] = [
      '#type' => 'number',
      '#title' => $this->t('Capture width (in pixels)'),
      '#description' => $this->t('Specify the width you would like to capture.'),
      // '#default_value' => $web_page_archive->isScreenshotCapturing(),
      // '#default_value' => TRUE,
      '#default_value' => $config["{$this->pluginId}_width"],
    ];

    return $form;
  }

}
