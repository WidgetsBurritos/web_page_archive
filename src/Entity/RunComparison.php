<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the run comparison entity.
 *
 * @ingroup web_page_archive
 *
 * @ContentEntityType(
 *   id = "wpa_run_comparison",
 *   label = @Translation("Web page archive run comparison"),
 *   handlers = {
 *     "storage" = "Drupal\web_page_archive\Entity\Sql\RunComparisonStorage",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\web_page_archive\Entity\RunComparisonViewsData",
 *     "form" = {
 *       "delete" = "Drupal\web_page_archive\Form\RunComparisonDeleteForm",
 *     },
 *   },
 *   base_table = "wpa_run_comparison",
 *   revision_table = "wpa_run_comparison_revision",
 *   revision_data_table = "wpa_run_comparison_field_revision",
 *   admin_permission = "administer web page archive",
 *   fieldable = TRUE,
 *   links = {
 *     "canonical" = "/admin/config/system/web-page-archive/compare/{wpa_run_comparison}",
 *     "add-form" = "/admin/config/system/web-page-archive/compare",
 *     "delete-form" = "/admin/config/system/web-page-archive/compare/{wpa_run_comparison}/delete",
 *     "collection" = "/admin/config/system/web-page-archive/compare/history"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   field_ui_base_route = "wpa_run_comparison.settings"
 * )
 */
class RunComparison extends RevisionableContentEntityBase implements RunComparisonInterface {

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
  public function getRun1() {
    return \Drupal::entityTypeManager()->getStorage('web_page_archive_run')->loadRevision($this->getRun1Id());
  }

  /**
   * {@inheritdoc}
   */
  public function getRun1Id() {
    return (int) $this->get('run1')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getRun2() {
    return \Drupal::entityTypeManager()->getStorage('web_page_archive_run')->loadRevision($this->getRun2Id());
  }

  /**
   * {@inheritdoc}
   */
  public function getRun2Id() {
    return (int) $this->get('run2')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getStripType() : string {
    return $this->get('strip_type')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getStripPatterns() : array {
    return !empty($this->getStripType()) ? $this->get('strip_patterns')->first()->getValue() : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getComparisonUtilities() : array {
    $first = $this->get('comparison_utilities')->first();
    return isset($first) ? $first->getValue() : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRunEntities() {
    return [$this->getRun1(), $this->getRun2()];
  }

  /**
   * {@inheritdoc}
   */
  public function getQueue() {
    $queue_name = "web_page_archive_compare.{$this->uuid()}";
    return \Drupal::service('queue')->get($queue_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getResultAtIndex($index) {
    return \Drupal::entityTypeManager()->getStorage('wpa_run_comparison')->getResultAtIndex($index);
  }

  /**
   * {@inheritdoc}
   */
  public function getResults() {
    return \Drupal::entityTypeManager()->getStorage('wpa_run_comparison')->getResults($this);
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
      ->setDescription(t('The name of the comparison.'))
      // ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 255,
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
      ->setLabel(t('Run status'))
      ->setDescription(t('A boolean indicating whether the run comparison is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['run1'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Run 1 ID'))
      ->setDescription(t('The first run ID.'))
      ->setRevisionable(FALSE);

    $fields['run2'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Run 2 ID'))
      ->setDescription(t('The second run ID.'))
      ->setRevisionable(FALSE);

    $fields['strip_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Strip type'))
      ->setDescription(t('Type of stripping to apply to urls/comparison keys.'))
      ->setRevisionable(FALSE);

    $fields['strip_patterns'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Strip patterns'))
      ->setDescription(t('Patterns to strip from urls/comparison keys.'))
      ->setRevisionable(FALSE);

    $fields['comparison_utilities'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Comparison utilities'))
      ->setDescription(t('List of comparison utilities to use.'))
      ->setRevisionable(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
