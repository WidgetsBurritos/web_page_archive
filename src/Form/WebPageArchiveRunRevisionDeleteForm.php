<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
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
  protected $webPageArchiveRunStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new WebPageArchiveRunRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   The cache tag invalidator service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage, CacheTagsInvalidatorInterface $cache_tag_invalidator, DateFormatterInterface $date_formatter) {
    $this->webPageArchiveRunStorage = $entity_storage;
    $this->cacheTagInvalidator = $cache_tag_invalidator;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity_type.manager');
    return new static(
      $entity_manager->getStorage('web_page_archive_run'),
      $container->get('cache_tags.invalidator'),
      $container->get('date.formatter')
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
    $replacements = [
      '%job' => $this->revision->label(),
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ];
    return $this->t('Are you sure you want to delete the %job run from %revision-date?', $replacements);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This job and all stored files will be removed from the system.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.web_page_archive.canonical', ['web_page_archive' => $this->revision->getConfigEntity()->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $web_page_archive_run_revision = NULL) {
    $this->revision = $this->webPageArchiveRunStorage->loadRevision($web_page_archive_run_revision);
    if (!$this->revision->getRetentionLocked()) {
      $form = parent::buildForm($form, $form_state);
    }
    else {
      $form['locked'] = [
        '#markup' => $this->t('This run cannot be deleted as it is current locked.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->revision->getRetentionLocked()) {
      $form_state->setErrorByName('locked', $this->t('This revision is locked and cannot be removed.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $wpa = $this->revision->getConfigEntity();
    $title = $this->revision->label();
    $revision_id = $this->revision->getRevisionId();
    if ($this->webPageArchiveRunStorage->deleteRevision($revision_id)) {
      $message = $this->t('%title run deleted with ID: %revision.', [
        '%title' => $title,
        '%revision' => $revision_id,
      ]);
      $this->logger('web_page_archive')->notice($message);
      $this->messenger()->addStatus($message);
      $this->cacheTagInvalidator->invalidateTags(['config:views.view.web_page_archive_canonical']);
    }
    else {
      $message = $this->t('%title could not delete run with ID: %revision. The most recent run cannot be deleted.', [
        '%title' => $title,
        '%revision' => $revision_id,
      ]);
      $this->logger('web_page_archive')->warning($message);
      $this->messenger()->addWarning($message);
    }
    $form_state->setRedirect(
      'entity.web_page_archive.canonical',
       ['web_page_archive' => $wpa->id()]
    );
  }

}
