<?php

namespace Drupal\web_page_archive\Entity\Sql;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface WebPageArchiveRunStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Web page archive run revision IDs for a specific run.
   *
   * @param \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface $entity
   *   The Web page archive run entity.
   *
   * @return int[]
   *   Web page archive run revision IDs (in ascending order).
   */
  public function revisionIds(WebPageArchiveRunInterface $entity);

  /**
   * Gets a list of all web page archive run revision IDs and labels.
   *
   * @return array
   *   Web page archive run revision ids and labels.
   */
  public function fullRevisionList();

  /**
   * Gets a list of revision IDs having a given user as run author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Web page archive run revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface $entity
   *   The Web page archive run entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(WebPageArchiveRunInterface $entity);

  /**
   * Unsets the language for all Web page archive run with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
