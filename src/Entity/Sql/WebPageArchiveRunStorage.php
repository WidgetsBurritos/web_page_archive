<?php

namespace Drupal\web_page_archive\Entity\Sql;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\web_page_archive\Entity\WebPageArchiveRunInterface;

/**
 * Defines the storage handler class for Web page archive run entities.
 *
 * This extends the base storage class, adding required special handling for
 * Web page archive run entities.
 *
 * @ingroup web_page_archive
 */
class WebPageArchiveRunStorage extends SqlContentEntityStorage implements WebPageArchiveRunStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(WebPageArchiveRunInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {web_page_archive_run_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function fullRevisionList() {
    return $this->database->query(
      'SELECT vid, name, revision_created FROM {web_page_archive_run_revision} ORDER BY vid'
    )->fetchAllAssoc('vid');
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {web_page_archive_run_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(WebPageArchiveRunInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {web_page_archive_run_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('web_page_archive_run_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
