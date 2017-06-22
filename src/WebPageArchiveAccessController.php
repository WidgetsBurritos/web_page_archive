<?php

namespace Drupal\web_page_archive;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the robot entity.
 *
 * We set this class to be the access controller in Robot's entity annotation.
 *
 * @see \Drupal\web_page_archive\Entity\WebPageArchive
 */
class WebPageArchiveAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if (in_array($operation, ['enable', 'disable'])) {
      return TRUE;
    }
    return parent::checkAccess($entity, $operation, $account);
  }

}
