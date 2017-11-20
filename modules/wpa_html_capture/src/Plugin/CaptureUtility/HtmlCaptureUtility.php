<?php

namespace Drupal\wpa_html_capture\Plugin\CaptureUtility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityBase;
use Drupal\wpa_html_capture\Plugin\CaptureResponse\HtmlCaptureResponse;

/**
 * Captures HTML of a remote uri.
 *
 * @CaptureUtility(
 *   id = "wpa_html_capture",
 *   label = @Translation("HTML capture utility", context = "Web Page Archive"),
 *   description = @Translation("Captures HTML for given URL", context = "Web Page Archive")
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
    // Handle missing URLs.
    if (!isset($data['url'])) {
      throw new \Exception('Capture URL is required');
    }

    // Determine file locations.
    $filename = $this->getFileName($data, 'html');

    // Save html and set our response.
    \Drupal::httpClient()->request('GET', $data['url'], ['sink' => $filename]);
    $this->response = new HtmlCaptureResponse($filename, $data['url']);

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
  public function defaultConfiguration() {
    $config = \Drupal::configFactory()->get('web_page_archive.wpa_html_capture.settings');
    return [
      'capture' => $config->get('defaults.capture'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['capture'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Capture Html?'),
      '#description' => $this->t('If checked, this archive will download and compare html.'),
      '#default_value' => $this->configuration['capture'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['capture'] = $form_state->getValue('capture');
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupRevision($revision_id) {
    HtmlCaptureResponse::cleanupRevision($revision_id);
  }

}
