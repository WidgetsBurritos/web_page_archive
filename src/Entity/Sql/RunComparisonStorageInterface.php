<?php

namespace Drupal\web_page_archive\Entity\Sql;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
   * Adds a result for the specified run comparison entity.
   *
   * @param \Drupal\web_page_archive\Entity\RunComparisonInterface $entity
   *   The run comparison entity.
   * @param mixed[] $result
   *   An array containing the result data.
   *
   * @return int
   *   ID of the added result.
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

  /**
   * Adds normalized variance data to the database.
   *
   * @param array $result
   *   Result array for adding to normalized variance table.
   */
  public function addNormalizedVariance(array $result);

  /**
   * Retrieves normalized variance data at the specified index.
   *
   * @param int $index
   *   The ID for the run comparison entity.
   *
   * @return mixed[]
   *   A list of matching results.
   */
  public function getNormalizedVarianceAtIndex($index);

}
