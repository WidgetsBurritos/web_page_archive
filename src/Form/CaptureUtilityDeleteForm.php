<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Entity\WebPageArchiveInterface;

/**
 * Form for deleting a capture utility.
 */
class CaptureUtilityDeleteForm extends ConfirmFormBase {

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
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the @utility capture utility from the %archive archive?', ['%archive' => $this->webPageArchive->label(), '@utility' => $this->captureUtility->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->webPageArchive->urlInfo('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_page_archive_capture_utility_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, WebPageArchiveInterface $web_page_archive = NULL, $capture_utility = NULL) {
    $this->webPageArchive = $web_page_archive;
    $this->captureUtility = $this->webPageArchive->getCaptureUtility($capture_utility);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->webPageArchive->deleteCaptureUtility($this->captureUtility);
    $this->messenger()->addStatus($this->t('The capture utility %name has been deleted.', ['%name' => $this->captureUtility->label()]));
    $form_state->setRedirectUrl($this->webPageArchive->urlInfo('edit-form'));
  }

}
