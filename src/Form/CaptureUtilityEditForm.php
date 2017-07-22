<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Entity\WebPageArchiveInterface;

/**
 * Provides an edit form for capture utilities.
 */
class CaptureUtilityEditForm extends CaptureUtilityFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, WebPageArchiveInterface $web_page_archive = NULL, $capture_utility = NULL) {
    $form = parent::buildForm($form, $form_state, $web_page_archive, $capture_utility);

    $form['#title'] = $this->t('Edit %label capture utility', ['%label' => $this->captureUtility->label()]);
    $form['actions']['submit']['#value'] = $this->t('Update capture utility');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCaptureUtility($capture_utility) {
    return $this->webPageArchive->getCaptureUtility($capture_utility);
  }

}
