<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Web page archive run entities.
 *
 * @ingroup web_page_archive
 */
class WebPageArchiveRunListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Web page archive run ID');
    $header['uuid'] = $this->t('Uuid');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\web_page_archive\Entity\WebPageArchiveRun */
    $row['id'] = $entity->id();
    $row['uuid'] = $entity->uuid();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.web_page_archive_run.edit_form',
      ['web_page_archive_run' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
