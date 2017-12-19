<?php

namespace Drupal\web_page_archive\Entity\Sql;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\web_page_archive\Entity\RunComparisonInterface;

/**
 * Defines the storage handler class for run comparison entities.
 *
 * This extends the base storage class, adding required special handling for
 * run comparison entities.
 *
 * @ingroup web_page_archive
 */
interface RunComparisonStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Web page archive run revision IDs for a specific run.
   *
   * @param \Drupal\web_page_archive\Entity\RunComparisonInterface $entity
   *   The run comparison entity.
   *
   * @return int[]
   *   Web page archive run revision IDs (in ascending order).
   */
  public function revisionIds(RunComparisonInterface $entity);

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
   * @param \Drupal\web_page_archive\Entity\RunComparisonInterface $entity
   *   The run comparison entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(RunComparisonInterface $entity);

  /**
   * Unsets the language for all Web page archive run with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

  /**
   * Adds a result for the specified run comparison entity.
   *
   * @param \Drupal\web_page_archive\Entity\RunComparisonInterface $entity
   *   The run comparison entity.
   * @param mixed[] $result
   *   An array containing the result data.
   */
  public function addResult(RunComparisonInterface $entity, array $result);

  /**
   * Retrieves the results for a specified run comparison entity.
   *
   * @param \Drupal\web_page_archive\Entity\RunComparisonInterface $entity
   *   The run comparison entity.
   *
   * @return mixed[]
   *   A list of matching results.
   */
  public function getResults(RunComparisonInterface $entity);

}
