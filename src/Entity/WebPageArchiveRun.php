<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

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
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\web_page_archive\Entity\WebPageArchiveRunListBuilder",
 *     "views_data" = "Drupal\web_page_archive\Entity\WebPageArchiveRunViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\web_page_archive\Form\WebPageArchiveRunForm",
 *       "add" = "Drupal\web_page_archive\Form\WebPageArchiveRunForm",
 *       "edit" = "Drupal\web_page_archive\Form\WebPageArchiveRunForm",
 *       "delete" = "Drupal\web_page_archive\Form\WebPageArchiveRunDeleteForm",
 *     },
 *     "access" = "Drupal\web_page_archive\Entity\WebPageArchiveRunAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\web_page_archive\Entity\Routing\WebPageArchiveRunHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "web_page_archive_run",
 *   revision_table = "web_page_archive_run_revision",
 *   revision_data_table = "web_page_archive_run_field_revision",
 *   admin_permission = "administer web page archive run entities",
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
 *   },
 *   links = {
 *     "canonical" = "/admin/config/system/web-page-archive/runs/{web_page_archive_run}",
 *     "add-form" = "/admin/config/system/web-page-archive/runs/add",
 *     "edit-form" = "/admin/config/system/web-page-archive/runs/{web_page_archive_run}/edit",
 *     "delete-form" = "/admin/config/system/web-page-archive/runs/{web_page_archive_run}/delete",
 *     "version-history" = "/admin/config/system/web-page-archive/runs/{web_page_archive_run}/revisions",
 *     "revision" = "/admin/config/system/web-page-archive/runs/{web_page_archive_run}/revisions/{web_page_archive_run_revision}/view",
 *     "revision_delete" = "/admin/config/system/web-page-archive/runs/{web_page_archive_run}/revisions/{web_page_archive_run_revision}/delete",
 *     "collection" = "/admin/config/system/web-page-archive/runs",
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

    // If no revision author has been set explicitly, make the web_page_archive_run owner the
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
    // TODO: Lock per entity?
    // TODO: More performant option here:
    // This get and append method gets slower as the list grows.
    $lock = \Drupal::lock();
    if ($lock->acquire('web_page_archive_run')) {
      $entity = \Drupal::service('entity.repository')->loadEntityByUuid('web_page_archive_run', $this->uuid());

      $field_captures = $entity->get('field_captures');
      $uuid = $this->uuidGenerator()->generate();
      $capture = [
        'uuid' => $uuid,
        'timestamp' => \Drupal::service('datetime.time')->getCurrentTime(),
        'status' => 'complete',
        'capture_url' => $data['url'],
        'capture_type' => $data['capture_response']->getType(),
        'content' => $data['capture_response']->getContent(),
      ];
      $field_captures->appendItem(serialize($capture));
      $entity->save();

      $lock->release('web_page_archive_run');
    }
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

    $fields['queue_ct'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Number of items in the queue.'))
      ->setDescription(t('A boolean indicating whether the Web page archive run is published.'))
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
    // TODO: Delete all stored files.
    parent::delete();
  }

}
