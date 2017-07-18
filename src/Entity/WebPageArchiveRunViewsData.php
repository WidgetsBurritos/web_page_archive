<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Web page archive run entities.
 */
class WebPageArchiveRunViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
