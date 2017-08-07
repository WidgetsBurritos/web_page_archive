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
    $data['web_page_archive_run']['id']['relationship']['id'] = 'standard';
    $data['web_page_archive_run']['id']['relationship']['base'] = 'web_page_archive_run_revision';
    $data['web_page_archive_run']['id']['relationship']['base field'] = 'id';
    $data['web_page_archive_run']['id']['relationship']['title'] = $this->t('Web Page Archive Run Revision');
    $data['web_page_archive_run']['id']['relationship']['label'] = $this->t('Get the web page archive run revision from a web page archive run.');

    $data['web_page_archive_run']['vid']['relationship']['id'] = 'standard';
    $data['web_page_archive_run']['vid']['relationship']['base'] = 'web_page_archive_run_revision';
    $data['web_page_archive_run']['vid']['relationship']['base field'] = 'vid';
    $data['web_page_archive_run']['vid']['relationship']['title'] = $this->t('Web Page Archive Run Revision');
    $data['web_page_archive_run']['vid']['relationship']['label'] = $this->t('Get the web page archive run revision from a web page archive run.');

    // Setup relationship from revisions to core entity.
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

    // Setup relationship to field captures.
    $data['web_page_archive_run_revision']['vid']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_revision']['vid']['relationship']['base'] = 'web_page_archive_run_revision__field_captures';
    $data['web_page_archive_run_revision']['vid']['relationship']['base field'] = 'revision_id';
    $data['web_page_archive_run_revision']['vid']['relationship']['title'] = $this->t('Web Page Archive Run Revision Captures');
    $data['web_page_archive_run_revision']['vid']['relationship']['label'] = $this->t('Get the web page archive run revision captures from a web page archive revision.');

    // Expose web_page_archive_run_revision__field_captures table to views.
    $data['web_page_archive_run_revision__field_captures'] = [];
    $data['web_page_archive_run_revision__field_captures']['table'] = [];
    $data['web_page_archive_run_revision__field_captures']['table']['group'] = t('Web Page Archive');

    // Expose the delta field.
    $data['web_page_archive_run_revision__field_captures']['delta'] = [
      'title' => t('Delta'),
      'help' => t('Delta.'),
      'relationship' => [
        'base' => 'web_page_archive_run_revision',
        'id' => 'standard',
        'label' => t('Delta'),
      ],
    ];

    // Expose the capture field in serialized format.
    $data['web_page_archive_run_revision__field_captures']['field_captures_value'] = [
      'title' => t('Capture (serialized)'),
      'help' => t('Capture (serialized).'),
      'relationship' => [
        'base' => 'web_page_archive_run_revision',
        'id' => 'standard',
        'label' => t('Capture (serialized)'),
      ],
      'field' => [
        'id' => 'web_page_archive_serialized_capture',
      ],
    ];

    // Setup relationship to web_page_archive_capture_details.
    $data['web_page_archive_run_revision__field_captures']['revision_id']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_revision__field_captures']['revision_id']['relationship']['base'] = 'web_page_archive_capture_details';
    $data['web_page_archive_run_revision__field_captures']['revision_id']['relationship']['base field'] = 'revision_id';
    $data['web_page_archive_run_revision__field_captures']['revision_id']['relationship']['title'] = $this->t('Web page archive capture details');
    $data['web_page_archive_run_revision__field_captures']['revision_id']['relationship']['label'] = $this->t('Additional details on an individual capture.');
    $data['web_page_archive_run_revision__field_captures']['revision_id']['relationship']['extra'] = [
      ['field' => 'delta', 'left_field' => 'delta'],
      ['field' => 'langcode', 'left_field' => 'langcode'],
    ];

    // Expose capture details table.
    $data['web_page_archive_capture_details'] = [];
    $data['web_page_archive_capture_details']['table'] = [];
    $data['web_page_archive_capture_details']['table']['group'] = t('Web page archive run');
    $data['web_page_archive_capture_details']['capture_url'] = [
      'title' => $this->t('Capture URL'),
      'help' => $this->t('The URL captured during a run.'),
      'argument' => ['id' => 'string'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'string'],
      'sort' => ['id' => 'standard'],
    ];

    return $data;
  }

}
