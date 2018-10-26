<?php

namespace Drupal\web_page_archive\Entity\Sql;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\web_page_archive\Entity\RunComparisonInterface;

/**
 * Defines the storage handler class for Web page archive run entities.
 *
 * This extends the base storage class, adding required special handling for
 * Web page archive run entities.
 *
 * @ingroup web_page_archive
 */
class RunComparisonStorage extends SqlContentEntityStorage implements RunComparisonStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(RunComparisonInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {run_comparison_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function fullRevisionList() {
    return $this->database->query(
      'SELECT vid, name, revision_created FROM {run_comparison_revision} ORDER BY vid'
    )->fetchAllAssoc('vid');
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {run_comparison_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(RunComparisonInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {run_comparison_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('run_comparison_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function addResult(RunComparisonInterface $entity, array $result) {
    $required_keys = [
      'delta1',
      'delta2',
      'has_left',
      'has_right',
      'langcode',
      'results',
      'run1',
      'run2',
      'url',
      'variance',
    ];
    foreach ($required_keys as $required_key) {
      if (!isset($result[$required_key])) {
        throw new \Exception($this->t('@key is required', ['@key' => $required_key]));
      }
    }

    $values = [
      'revision_id' => $entity->getRevisionId(),
      'run1' => (int) $result['run1'],
      'run2' => (int) $result['run2'],
      'delta1' => (int) $result['delta1'],
      'delta2' => (int) $result['delta2'],
      'has_left' => (int) $result['has_left'],
      'has_right' => (int) $result['has_right'],
      'langcode' => $result['langcode'],
      'url' => $result['url'],
      'variance' => (float) $result['variance'],
      'results' => $result['results'],
      'timestamp' => \Drupal::time()->getRequestTime(),
    ];

    $this->database
      ->insert('web_page_archive_run_comparison_details')
      ->fields(array_keys($values))
      ->values(array_values($values))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getResultAtIndex($index) : array {
    $query = $this->database->query(
      'SELECT * FROM {web_page_archive_run_comparison_details} WHERE cid=:cid ORDER BY url',
      [':cid' => $index]);
    return $query->fetchAssoc();
  }

  /**
   * {@inheritdoc}
   */
  public function getResults(RunComparisonInterface $entity) {
    $query = $this->database->query(
      'SELECT * FROM {web_page_archive_run_comparison_details} WHERE revision_id=:revision_id ORDER BY url',
      [':revision_id' => $entity->getRevisionId()]);
    $rows = [];
    while ($row = $query->fetchAssoc()) {
      $rows[$row['cid']] = $row;
    }

    return $rows;
  }

}
