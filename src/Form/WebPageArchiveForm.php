<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class WebPageArchiveForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $web_page_archive = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $web_page_archive->label(),
      '#description' => $this->t("Label for the Web page archive entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $web_page_archive->id(),
      '#machine_name' => [
        'exists' => '\Drupal\web_page_archive\Entity\WebPageArchive::load',
      ],
      '#disabled' => !$web_page_archive->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $web_page_archive = $this->entity;
    $status = $web_page_archive->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Web page archive entity.', [
          '%label' => $web_page_archive->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Web page archive entity.', [
          '%label' => $web_page_archive->label(),
        ]));
    }
    $form_state->setRedirectUrl($web_page_archive->toUrl('collection'));
  }

}
