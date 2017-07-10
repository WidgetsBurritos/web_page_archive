<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manipulates entity type information.
 *
 * This class contains primarily bridged hooks for compile-time or
 * cache-clear-time hooks. Runtime hooks should be placed in EntityOperations.
 */
class WebPageArchiveTypeInfo implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * EntityTypeInfo constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Adds links to web_page_archive entities.
   *
   * This is an alter hook bridge.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface[] $entity_types
   *   The master entity type list to alter.
   *
   * @see hook_entity_type_alter()
   */
  public function entityTypeAlter(array &$entity_types) {
    $entity_type = $entity_types['web_page_archive'];
    $entity_type->setLinkTemplate('canonical', "/admin/config/development/web-page-archive/{web_page_archive}");
    $entity_type->setLinkTemplate('queue_form', "/admin/config/development/web-page-archive/{web_page_archive}/queue");
  }

  /**
   * Adds operations on web_page_archive entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity on which to define an operation.
   *
   * @return array
   *   An array of operation definitions.
   *
   * @see hook_entity_operation()
   */
  public function entityOperation(EntityInterface $entity) {
    $operations = [];
    if ($this->currentUser->hasPermission('access web_page_archive information')) {
      if ($entity->hasLinkTemplate('canonical')) {
        $operations['web_page_archive_view'] = [
          'title' => $this->t('View Run History'),
          'weight' => -1,
          'url' => $entity->toUrl('canonical'),
        ];
      }
      if ($entity->hasLinkTemplate('queue_form')) {
        $title = $entity->getQueueCt() > 0 ? $this->t('Queue') : $this->t('Start Run');

        $operations['web_page_archive_queue'] = [
          'title' => $title,
          'weight' => 0,
          'url' => $entity->toUrl('queue_form'),
        ];
      }

    }
    return $operations;
  }

}