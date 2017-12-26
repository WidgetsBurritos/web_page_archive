<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\web_page_archive\Controller\CleanupController;

/**
 * Defines the Web page archive run entity.
 *
 * @ingroup web_page_archive
 *
 * @ContentEntityType(
 *   id = "web_page_archive_run",
 *   label = @Translation("Web page archive run"),
 *   handlers = {
 *     "storage" = "Drupal\web_page_archive\Entity\Sql\WebPageArchiveRunStorage",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\web_page_archive\Entity\WebPageArchiveRunViewsData",
 *
 *     "form" = {
 *       "delete" = "Drupal\web_page_archive\Form\WebPageArchiveRunDeleteForm",
 *     },
 *   },
 *   base_table = "web_page_archive_run",
 *   revision_table = "web_page_archive_run_revision",
 *   revision_data_table = "web_page_archive_run_field_revision",
 *   admin_permission = "administer web page archive",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "queue_ct" = "queue_ct",
 *     "success_ct" = "success_ct",
 *     "capture_utilities" = "capture_utilities",
 *   },
 *   field_ui_base_route = "web_page_archive_run.settings"
 * )
 */
class WebPageArchiveRun extends RevisionableContentEntityBase implements WebPageArchiveRunInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the entity owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigEntity() {
    $entities = $this->get('config_entity')->referencedEntities();
    if (!empty($entities)) {
      return $entities[0];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueCt() {
    return $this->get('queue_ct');
  }

  /**
   * Sets number of items in the queue.
   */
  public function setQueueCt($ct) {
    $this->set('queue_ct', $ct);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuccessCt() {
    return (int) $this->get('success_ct')->getString();
  }

  /**
   * Sets number of jobs successfully ran.
   */
  public function setSuccessCt($ct) {
    $this->set('success_ct', $ct);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setCompleted($completed) {
    $this->set('status', $completed ? TRUE : FALSE);
    return $this;
  }

  /**
   * Retrieves capture utilties used in this run.
   */
  public function getCaptureUtilities() {
    return $this->get('capture_utilities');
  }

  /**
   * Sets the capture utilities.
   */
  public function setCaptureUtilities(array $array) {
    $this->set('capture_utilities', $array);
    return $this;
  }

  /**
   * Retrieves list of captured data.
   */
  public function getCapturedArray() {
    return $this->get('field_captures');
  }

  /**
   * Sets the captured array.
   */
  public function setCapturedArray(array $array) {
    $this->set('field_captures', $array);
    return $this;
  }

  /**
   * Marks a capture task complete.
   */
  public function markCaptureComplete($data) {
    // TODO: Lock acquired too late?
    // TODO: More performant option here:
    // This get and append method gets slower as the list grows.
    $lock = \Drupal::lock();
    $lock_id = "web_page_archive_run:{$this->uuid()}";
    if ($lock->acquire($lock_id)) {
      $entity = \Drupal::service('entity.repository')->loadEntityByUuid('web_page_archive_run', $this->uuid());
      $success_ct = $entity->getSuccessCt();
      $field_captures = $entity->get('field_captures');
      $total_capture_size = (int) $entity->get('capture_size')->getString();
      $uuid = $this->uuidGenerator()->generate();
      try {
        $capture_size = $data['capture_response']->getCaptureSize();
        $success_ct++;
      }
      catch (\Exception $e) {
        // TODO: What to do here? Error log?
        $capture_size = 0;
      }
      $capture = [
        'uuid' => $uuid,
        'run_uuid' => $data['run_uuid'],
        'timestamp' => \Drupal::service('datetime.time')->getCurrentTime(),
        'status' => 'complete',
        'capture_url' => $data['url'],
        'capture_response' => $data['capture_response'],
        'capture_size' => $capture_size,
        'vid' => $entity->getRevisionId(),
        'delta' => $field_captures->count(),
        'langcode' => $entity->language()->getId(),
      ];
      $field_captures->appendItem(serialize($capture));
      $entity->setSuccessCt($success_ct);
      $entity->set('capture_size', $total_capture_size + $capture_size);
      $entity->save();
      $this->addNormalizedCapture($capture);
      $timeout = $this->getConfigEntity()->getTimeout();
      usleep(1000 * $timeout);
      $tags = [
        'config:views.view.web_page_archive_canonical',
        'config:views.view.web_page_archive_individual',
      ];
      \Drupal::service('cache_tags.invalidator')->invalidateTags($tags);
      $lock->release($lock_id);
    }
  }

  /**
   * Adds a normalized version of a capture to the database table.
   */
  public function addNormalizedCapture($capture) {
    \Drupal::database()
      ->insert('web_page_archive_capture_details')
      ->fields(['revision_id', 'delta', 'langcode', 'capture_url'])
      ->values([
        $capture['vid'],
        $capture['delta'],
        $capture['langcode'],
        $capture['capture_url'],
      ])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Web page archive run entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Web page archive run entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Web page archive run is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['capture_utilities'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Capture utilities'))
      ->setDescription(t('A list of capture utilities used for this run.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue([])
      ->setDisplayOptions('view', [
        'type' => 'web_page_archive_capture_utility_map_formatter',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'web_page_archive_capture_utility_map_widget',
        'weight' => -4,
      ]);

    $fields['queue_ct'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Queue count'))
      ->setDescription(t('Total number of tasks in the queue.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(0);

    $fields['success_ct'] = BaseFieldDefinition::create('wpa_default_integer')
      ->setLabel(t('Success count'))
      ->setDescription(t('Number of successfully completed in the queue.'))
      ->setRevisionable(TRUE);

    $fields['capture_size'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Capture size'))
      ->setDescription(t('A floating point number representing cumulative file size for a run.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(0);

    $fields['config_entity'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Config entity'))
      ->setDescription(t('The ID of web page archive configuration entity.'))
      ->setRevisionable(FALSE)
      ->setSetting('target_type', 'web_page_archive')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    if (!$this->isNew()) {
      CleanupController::cleanRunEntity($this);
    }
    parent::delete();
  }

}
