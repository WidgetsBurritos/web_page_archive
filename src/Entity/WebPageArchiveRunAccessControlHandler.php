<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Web page archive run entity.
 *
 * @see \Drupal\web_page_archive\Entity\WebPageArchiveRun.
 */
class WebPageArchiveRunAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished web page archive run entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published web page archive run entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit web page archive run entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete web page archive run entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add web page archive run entities');
  }

}
