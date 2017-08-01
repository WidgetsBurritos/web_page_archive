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

    $data['web_page_archive_run_revision']['capture_utilities']['filter']['id'] = 'web_page_archive_capture_utility_filter';

    // We need to setup relationship from revision to entity, until core issue
    // is resolved.
    // @see https://www.drupal.org/node/2652652
    $data['web_page_archive_run_revision']['id']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_revision']['id']['relationship']['base'] = 'web_page_archive_run';
    $data['web_page_archive_run_revision']['id']['relationship']['base field'] = 'id';
    $data['web_page_archive_run_revision']['id']['relationship']['title'] = $this->t('Web Page Archive Run');
    $data['web_page_archive_run_revision']['id']['relationship']['label'] = $this->t('Get the web page archive run from a web page archive run revision.');

    $data['web_page_archive_run_revision']['revision_id']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_revision']['revision_id']['relationship']['base'] = 'web_page_archive_run';
    $data['web_page_archive_run_revision']['revision_id']['relationship']['base field'] = 'revision_id';
    $data['web_page_archive_run_revision']['revision_id']['relationship']['title'] = $this->t('Web Page Archive Run');
    $data['web_page_archive_run_revision']['revision_id']['relationship']['label'] = $this->t('Get the web page archive run from a web page archive run revision.');

    return $data;
  }

}
