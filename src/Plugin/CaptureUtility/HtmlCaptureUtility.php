<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\CaptureResponse\HtmlCaptureResponse;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityBase;

/**
 * Captures HTML of a remote uri.
 *
 * @CaptureUtility(
 *   id = "html_capture_utility",
 *   label = @Translation("Html capture utility", context = "Web Page Archive"),
 * )
 */
class HtmlCaptureUtility extends ConfigurableCaptureUtilityBase {

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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['capture'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Capture Html?'),
      '#description' => $this->t('If checked, this archive will download and compare html.'),
      // '#default_value' => $web_page_archive->isScreenshotCapturing(),
      // '#default_value' => $config[$this->pluginId],
      // TODO: How to determine this?
      '#default_value' => TRUE,
    ];
    return $form;
  }

}
