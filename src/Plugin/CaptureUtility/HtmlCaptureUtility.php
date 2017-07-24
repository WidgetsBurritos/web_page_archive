<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityBase;

/**
 * Captures HTML of a remote uri.
 *
 * @CaptureUtility(
 *   id = "html_capture_utility",
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
    $file_path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $save_dir = "{$file_path}/web-page-archive/html/{$data['web_page_archive']->id()}/{$data['run_uuid']}";
    $file_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($data['url'])) . '.html';
    $file_location = "{$save_dir}/{$file_name}";

    if (!file_prepare_directory($save_dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      throw new \Exception("Could not write to $save_dir");
    }

    \Drupal::httpClient()->request('GET', $data['url'], ['sink' => $file_location]);

    $this->response = new UriCaptureResponse($file_location);

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

}
