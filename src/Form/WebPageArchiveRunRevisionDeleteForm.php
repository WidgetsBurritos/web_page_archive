<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Web page archive run revision.
 *
 * @ingroup web_page_archive
 */
class WebPageArchiveRunRevisionDeleteForm extends ConfirmFormBase {

  use MessengerTrait;

  /**
   * The Web page archive run revision.
   *
   * @var \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface
   */
  protected $revision;

  /**
   * The Web page archive run storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $WebPageArchiveRunStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new WebPageArchiveRunRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection) {
    $this->WebPageArchiveRunStorage = $entity_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity_type.manager');
    return new static(
      $entity_manager->getStorage('web_page_archive_run'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_page_archive_run_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => format_date($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.web_page_archive_run.version_history', ['web_page_archive_run' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $web_page_archive_run_revision = NULL) {
    $this->revision = $this->WebPageArchiveRunStorage->loadRevision($web_page_archive_run_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->WebPageArchiveRunStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Web page archive run: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addStatus(t('Revision from %revision-date of Web page archive run %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.web_page_archive_run.canonical',
       ['web_page_archive_run' => $this->revision->id()]
    );
  }

}
