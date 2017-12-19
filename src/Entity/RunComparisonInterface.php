<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Web page archive run entities.
 *
 * @ingroup web_page_archive
 */
interface RunComparisonInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Web page archive run name.
   *
   * @return string
   *   Name of the Web page archive run.
   */
  public function getName();

  /**
   * Sets the Web page archive run name.
   *
   * @param string $name
   *   The Web page archive run name.
   *
   * @return \Drupal\web_page_archive\Entity\RunComparisonInterface
   *   The called run comparison entity.
   */
  public function setName($name);

  /**
   * Gets the Web page archive run creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Web page archive run.
   */
  public function getCreatedTime();

  /**
   * Sets the Web page archive run creation timestamp.
   *
   * @param int $timestamp
   *   The Web page archive run creation timestamp.
   *
   * @return \Drupal\web_page_archive\Entity\RunComparisonInterface
   *   The called run comparison entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Web page archive run revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Web page archive run revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\web_page_archive\Entity\RunComparisonInterface
   *   The called run comparison entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Web page archive run revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Web page archive run revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\web_page_archive\Entity\RunComparisonInterface
   *   The called run comparison entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Retrieves the first run entity for the comparison.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The first run entity for this comparison.
   */
  public function getRun1();

  /**
   * Retrieves the first run entity id for the comparison.
   *
   * @return int
   *   The first run entity id for this comparison.
   */
  public function getRun1Id();

  /**
   * Retrieves the second run entity for the comparison.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The second run entity for this comparison.
   */
  public function getRun2();

  /**
   * Retrieves the first run entity id for the comparison.
   *
   * @return int
   *   The first run entity id for this comparison.
   */
  public function getRun2Id();

  /**
   * Retrieves both run entities as an array.
   *
   * @return RunComparisonInterface[]
   *   Retrieves both run comparison entities.
   */
  public function getRunEntities();

  /**
   * Retrieves a queue for the run comparison object.
   *
   * @return \Drupal\Core\Queue\QueueInterface
   *   The queue for this run comparison.
   */
  public function getQueue();

  /**
   * Retrieves list of comparison results.
   *
   * @return array
   *   A list of comparison results.
   */
  public function getResults();

}
