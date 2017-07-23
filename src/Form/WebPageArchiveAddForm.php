<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class WebPageArchiveAddForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveAddForm extends WebPageArchiveFormBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message($this->t('Created the %label Web page archive entity.', [
      '%label' => $this->entity->label(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create new archive');

    return $actions;
  }

}
