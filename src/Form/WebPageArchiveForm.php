<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Web Page Archive add and edit forms.
 */
class WebPageArchiveForm extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * Constructs a WebPageArchiveForm.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'));
  }

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
      '#default_value' => $web_page_archive->label,
      '#description' => $this->t('Label for the web page archive.'),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $web_page_archive->id,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
      '#disabled' => !$web_page_archive->isNew(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $web_page_archive = $this->entity;
    $status = $web_page_archive->save();

    if ($status) {
      drupal_set_message($this->t('The %label web page archive was saved.', [
        '%label' => $web_page_archive->label(),
      ]));
    }
    else {
      drupal_set_message($this->t('The %label web page archive was not saved.', [
        '%label' => $web_page_archive->label(),
      ]));
    }

    $form_state->setRedirect('entity.web_page_archive.collection');
  }

  /**
   * Checks for an existing archive.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new robot entity query.
    $query = $this->entityQueryFactory->get('web_page_archive');

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

}
