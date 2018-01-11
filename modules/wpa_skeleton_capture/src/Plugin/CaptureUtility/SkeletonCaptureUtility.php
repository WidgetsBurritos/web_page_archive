<?php

namespace Drupal\wpa_skeleton_capture\Plugin\CaptureUtility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityBase;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;

/**
 * Skeleton capture utility, useful for creating new plugins.
 *
 * @CaptureUtility(
 *   id = "wpa_skeleton_capture",
 *   label = @Translation("Skeleton capture utility", context = "Web Page Archive"),
 *   description = @Translation("Does nothing, but illustrates how capture utilities work", context = "Web Page Archive")
 * )
 */
class SkeletonCaptureUtility extends ConfigurableCaptureUtilityBase {

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
    // Configuration data is stored in $this->configuration. For example:
    $width = (int) $this->configuration['width'];

    // Note: The skeleton plugin uses the basic UriCaptureResponse storage
    // mechanism, which simply stores a URI path to some file containing the
    // contents of your capture. If you wish to use an alternative response
    // mechanism, such as storing the data directly inside the database table
    // instead of external files, you simply need to create a response class
    // that extends \Drupal\web_page_archive\Plugin\CaptureResponseBase.
    $response_content = $this->t('Contents from @url', ['@url' => $data['url']]);
    $this->response = new UriCaptureResponse($response_content, $data['url']);

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
    $config = \Drupal::configFactory()->get('web_page_archive.wpa_skeleton_capture.settings');
    return [
      'width' => $config->get('defaults.width'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Use the Form API to create fields. Each field should have corresponding
    // entry in your config/module.schema.yml file.
    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Capture width.'),
      '#default_value' => $this->configuration['width'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['width'] = $form_state->getValue('width');
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupRevision($revision_id) {
    UriCaptureResponse::cleanupRevision($revision_id);
  }

}
