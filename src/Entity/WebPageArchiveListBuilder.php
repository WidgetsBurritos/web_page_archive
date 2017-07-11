<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Web page archive entity entities.
 */
class WebPageArchiveListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Web page archive entity');
    $header['id'] = $this->t('Machine name');
    $header['runs'] = $this->t('Runs');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['runs'] = $this->formatPlural($entity->getRunCt(), '1 run', '@count runs');
    $capture_ct = $entity->getQueueCt();
    if ($capture_ct) {
      $row['status'] = $this->formatPlural($capture_ct, '1 job in queue', '@count jobs in queue');
    }
    else {
      $row['status'] = $this->t('No pending jobs');
    }
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
