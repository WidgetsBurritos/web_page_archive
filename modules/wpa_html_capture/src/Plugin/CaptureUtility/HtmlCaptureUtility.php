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
    $entity_id = $data['run_entity']->getConfigEntity()->id();
    $file_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($data['url'])) . '.html';
    $file_path = $this->storagePath($entity_id, $data['run_uuid']) . '/' . $file_name;

    // Save html and set our response.
    \Drupal::httpClient()->request('GET', $data['url'], ['sink' => $file_path]);
    $this->response = new HtmlCaptureResponse($file_path, $data['url']);

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
    return [
      'capture' => TRUE,
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
