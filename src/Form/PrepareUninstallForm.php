<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Removes fields and data used by web_page_archive.
 */
class PrepareUninstallForm extends FormBase {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_page_archive_admin_prepare_uninstall';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['web_page_archive'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Prepare uninstall'),
      '#description' => $this->t('When clicked all web_page_archive data (content, fields) will be removed.'),
    ];

    $form['web_page_archive']['prepare_uninstall'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete web_page_archive data'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => t('Prepare for uninstall.'),
      'operations' => [
        [
          'Drupal\web_page_archive\Controller\PrepareUninstallController::deleteRunEntities', [],
        ],
        [
          'Drupal\web_page_archive\Controller\PrepareUninstallController::removeFields', [],
        ],
      ],
      'progress_message' => static::t('Deleting web_page_archive data... Completed @percentage% (@current of @total).'),
    ];
    batch_set($batch);

    $this->messenger()->addStatus($this->t('web_page_archive data has been deleted.'));
  }

}
