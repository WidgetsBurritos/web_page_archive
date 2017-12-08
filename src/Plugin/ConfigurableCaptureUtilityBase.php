<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a base class for configurable capture utilities.
 *
 * @see \Drupal\web_page_archive\Annotation\CaptureUtility
 * @see \Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityInterface
 * @see \Drupal\web_page_archive\Plugin\CaptureUtilityInterface
 * @see \Drupal\web_page_archive\Plugin\CaptureUtilityBase
 * @see \Drupal\web_page_archive\Plugin\CaptureUtilityManager
 * @see plugin_api
 */
abstract class ConfigurableCaptureUtilityBase extends CaptureUtilityBase implements ConfigurableCaptureUtilityInterface {

  /**
   * {@inheritdoc}
   */
  public function buildSystemSettingsForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormDescriptionLinkFromUrl($url, $label) {
    $urlObj = Url::fromUri($url);
    $urlObj->setOptions(['attributes' => ['target' => '_blank']]);
    return Link::fromTextAndUrl($label, $urlObj)->toString();
  }

}
