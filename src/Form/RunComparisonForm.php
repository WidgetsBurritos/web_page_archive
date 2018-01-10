<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Uuid\Php;
use Drupal\web_page_archive\Controller\RunComparisonController;
use Drupal\web_page_archive\Entity\Sql\WebPageArchiveRunStorageInterface;
use Drupal\web_page_archive\Entity\Sql\RunComparisonStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Compares web page archive capture runs.
 */
class RunComparisonForm extends FormBase {

  private $runStorage;
  private $runComparisonStorage;
  private $uuid;

  /**
   * Constructs a base class for web page archive add and edit forms.
   *
   * @param \Drupal\web_page_archive\Entity\Sql\WebPageArchiveRunStorageInterface $run_storage
   *   The run entity storage service.
   * @param \Drupal\web_page_archive\Entity\Sql\RunComparisonStorageInterface $run_comparison_storage
   *   The run comparison entity storage service.
   * @param \Drupal\Component\Uuid\Php $uuid
   *   The UUID generator service.
   */
  public function __construct(WebPageArchiveRunStorageInterface $run_storage, RunComparisonStorageInterface $run_comparison_storage, Php $uuid) {
    $this->runStorage = $run_storage;
    $this->runComparisonStorage = $run_comparison_storage;
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('web_page_archive_run'),
      $entity_type_manager->getStorage('wpa_run_comparison'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_page_archive_compare_runs';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#button_type' => 'primary',
      ],
    ];

    $form['run1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Run #1'),
      '#description' => $this->t('Specify the revision ID for the run you wish to use as your baseline.'),
      '#autocomplete_route_name' => 'web_page_archive.autocomplete.runs',
      '#required' => TRUE,
    ];
    $form['run2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Run #2'),
      '#description' => $this->t('Specify the revision ID for the run you wish to compare against.'),
      '#autocomplete_route_name' => 'web_page_archive.autocomplete.runs',
      '#required' => TRUE,
    ];
    $form['strip_type'] = [
      '#type' => 'select',
      '#title' => $this->t('URL/key stripping type'),
      '#options' => [
        '' => $this->t('None'),
        'string' => $this->t('String-based'),
        'regex' => $this->t('RegEx-based'),
      ],
      '#description' => $this->t('If comparing across hosts (e.g. www.mysite.com vs staging.mysite.com), you can strip portions of the URL or comparison key off.'),
    ];
    $form['strip_patterns'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URL/key stripping patterns'),
      '#description' => $this->t('Enter pattern(s) you would like stripped from comparison key. One pattern per line.'),
      '#states' => [
        'visible' => [
          ['select[name="strip_type"]' => ['value' => 'string']],
          ['select[name="strip_type"]' => ['value' => 'regex']],
        ],
        'required' => [
          ['select[name="strip_type"]' => ['value' => 'string']],
          ['select[name="strip_type"]' => ['value' => 'regex']],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Ensure both specified runs are valid.
    $run1 = $this->runStorage->loadRevision($form_state->getValue('run1'));
    if (!isset($run1)) {
      $form_state->setErrorByName('run1', $this->t('Run #1 must reference a valid web page archive run revision id.'));
    }
    $run2 = $this->runStorage->loadRevision($form_state->getValue('run2'));
    if (!isset($run2)) {
      $form_state->setErrorByName('run2', $this->t('Run #2 must reference a valid web page archive run revision id.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $run1 = $this->runStorage->loadRevision($form_state->getValue('run1'));
    $run2 = $this->runStorage->loadRevision($form_state->getValue('run2'));
    $labels = [
      '@label1' => RunComparisonController::generateRevisionLabel($run1->getRevisionId(), $run1->label(), $run1->getRevisionCreationTime()),
      '@label2' => RunComparisonController::generateRevisionLabel($run2->getRevisionId(), $run2->label(), $run2->getRevisionCreationTime()),
    ];
    $strip_type = $form_state->getValue('strip_type');
    $strip_patterns = !empty($strip_type) ? array_map('trim', explode(PHP_EOL, trim($form_state->getValue('strip_patterns')))) : [];

    $data = [
      'user_id' => \Drupal::currentUser()->id(),
      'name' => $this->t('@label1 -vs- @label2', $labels),
      'uuid' => $this->uuid->generate(),
      'run1' => $run1->getRevisionId(),
      'run2' => $run2->getRevisionId(),
      'status' => 1,
      'strip_type' => $strip_type,
      'strip_patterns' => serialize($strip_patterns),
    ];
    $run_comparison = $this->runComparisonStorage->create($data);
    $run_comparison->save();

    RunComparisonController::enqueueRunComparisons($run_comparison);
    RunComparisonController::setBatch($run_comparison);

    $form_state->setRedirect('entity.wpa_run_comparison.canonical', ['wpa_run_comparison' => $run_comparison->id()]);
  }

}
