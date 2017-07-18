<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Web page archive run edit forms.
 *
 * @ingroup web_page_archive
 */
class WebPageArchiveRunForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\web_page_archive\Entity\WebPageArchiveRun */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Web page archive run.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Web page archive run.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.web_page_archive_run.canonical', ['web_page_archive_run' => $entity->id()]);
  }

}
