<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\web_page_archive\Entity\WebPageArchiveInterface;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a base form for capture utilities.
 */
abstract class CaptureUtilityFormBase extends FormBase {

  use MessengerTrait;

  /**
   * The web page archive.
   *
   * @var \Drupal\web_page_archive\Entity\WebPageArchiveInterface
   */
  protected $webPageArchive;

  /**
   * The capture utility.
   *
   * @var \Drupal\web_page_archive\Plugin\CaptureUtilityInterface|\Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityInterface
   */
  protected $captureUtility;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'capture_utility_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\web_page_archive\Entity\WebPageArchiveInterface $web_page_archive
   *   The web page archive.
   * @param string $capture_utility
   *   The capture utility ID.
   *
   * @return array
   *   The form structure.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function buildForm(array $form, FormStateInterface $form_state, WebPageArchiveInterface $web_page_archive = NULL, $capture_utility = NULL) {
    $this->webPageArchive = $web_page_archive;
    try {
      $this->captureUtility = $this->prepareCaptureUtility($capture_utility);
    }
    catch (PluginNotFoundException $e) {
      throw new NotFoundHttpException("Invalid capture utility id: '$capture_utility'.");
    }
    $request = $this->getRequest();

    if (!($this->captureUtility instanceof ConfigurableCaptureUtilityInterface)) {
      throw new NotFoundHttpException();
    }

    $form['#attached']['library'][] = 'web_page_archive/admin';
    $form['uuid'] = [
      '#type' => 'value',
      '#value' => $this->captureUtility->getUuid(),
    ];
    $form['id'] = [
      '#type' => 'value',
      '#value' => $this->captureUtility->getPluginId(),
    ];

    $form['data'] = [];
    $subform_state = SubformState::createForSubform($form['data'], $form, $form_state);
    $form['data'] = $this->captureUtility->buildConfigurationForm($form['data'], $subform_state);
    $form['data']['#tree'] = TRUE;

    // Check the URL for a weight, then the capture utility, or use default.
    $form['weight'] = [
      '#type' => 'hidden',
      '#value' => $request->query->has('weight') ? (int) $request->query->get('weight') : $this->captureUtility->getWeight(),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $this->webPageArchive->urlInfo('edit-form'),
      '#attributes' => ['class' => ['button']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // The capture utility config is stored in the 'data' key in the form,
    // pass that through for validation.
    $this->captureUtility->validateConfigurationForm($form['data'], SubformState::createForSubform($form['data'], $form, $form_state));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    // The capture utility config is stored in the 'data' key in the form,
    // pass that through for submission.
    $this->captureUtility->submitConfigurationForm($form['data'], SubformState::createForSubform($form['data'], $form, $form_state));

    $this->captureUtility->setWeight($form_state->getValue('weight'));
    if (!$this->captureUtility->getUuid()) {
      $this->webPageArchive->addCaptureUtility($this->captureUtility->getConfiguration());
    }
    $this->webPageArchive->save();

    $this->messenger()->addStatus($this->t('The capture utility was successfully applied.'));
    $form_state->setRedirectUrl($this->webPageArchive->urlInfo('edit-form'));
  }

  /**
   * Converts an capture utility ID into an object.
   *
   * @param string $capture_utility
   *   The capture utility ID.
   *
   * @return \Drupal\web_page_archive\Entity\CaptureUtilityInterface
   *   The capture utility object.
   */
  abstract protected function prepareCaptureUtility($capture_utility);

}
