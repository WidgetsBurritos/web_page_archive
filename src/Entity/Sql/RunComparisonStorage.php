<?php

namespace Drupal\web_page_archive\Entity\Sql;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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

    return $this->database
      ->insert('web_page_archive_run_comparison_details')
      ->fields(array_keys($values))
      ->values(array_values($values))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function addNormalizedVariance(array $result) {
    $required_keys = [
      'cid',
      'response_index',
      'plugin_id',
      'variance',
    ];
    foreach ($required_keys as $required_key) {
      if (!isset($result[$required_key])) {
        throw new \Exception($this->t('@key is required', ['@key' => $required_key]));
      }
    }

    $values = [
      'cid' => (int) $result['cid'],
      'response_index' => (int) $result['response_index'],
      'plugin_id' => $result['plugin_id'],
      'variance' => (float) $result['variance'],
    ];

    $this->database
      ->insert('web_page_archive_comparison_variance')
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

  /**
   * {@inheritdoc}
   */
  public function getNormalizedVarianceAtIndex($index) : array {
    $query = $this->database->query(
      'SELECT * FROM {web_page_archive_comparison_variance} WHERE cid=:cid',
      [':cid' => $index]);
    return $query->fetchAll();
  }

}
