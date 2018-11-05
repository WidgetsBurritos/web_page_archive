<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides views data for run comparison entities.
 */
class RunComparisonViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Force ID field to use custom argument.
    $data['wpa_run_comparison']['id']['argument']['id'] = 'wpa_cid';

    // Setup run relationships.
    $data['wpa_run_comparison']['run1']['relationship']['id'] = 'standard';
    $data['wpa_run_comparison']['run1']['relationship']['base'] = 'web_page_archive_run_revision';
    $data['wpa_run_comparison']['run1']['relationship']['base field'] = 'vid';
    $data['wpa_run_comparison']['run1']['relationship']['title'] = $this->t('Web Page Archive Run #1');
    $data['wpa_run_comparison']['run1']['relationship']['label'] = $this->t('The first web page archive run related to this comparison entity.');

    $data['wpa_run_comparison']['run2']['relationship']['id'] = 'standard';
    $data['wpa_run_comparison']['run2']['relationship']['base'] = 'web_page_archive_run_revision';
    $data['wpa_run_comparison']['run2']['relationship']['base field'] = 'vid';
    $data['wpa_run_comparison']['run2']['relationship']['title'] = $this->t('Web Page Archive Run #2');
    $data['wpa_run_comparison']['run2']['relationship']['label'] = $this->t('The second web page archive run related to this comparison entity.');

    // Setup relationship to comparison details table.
    $data['wpa_run_comparison']['vid']['relationship']['id'] = 'standard';
    $data['wpa_run_comparison']['vid']['relationship']['base'] = 'web_page_archive_run_comparison_details';
    $data['wpa_run_comparison']['vid']['relationship']['base field'] = 'revision_id';
    $data['wpa_run_comparison']['vid']['relationship']['title'] = $this->t('Web Page Archive Run Comparison Details');
    $data['wpa_run_comparison']['vid']['relationship']['label'] = $this->t('The web page archive run comparison details.');

    // Expose web_page_archive_run_comparison_details table and fields to views.
    $data['web_page_archive_run_comparison_details'] = [];
    $data['web_page_archive_run_comparison_details']['table'] = [];
    $data['web_page_archive_run_comparison_details']['table']['group'] = t('Web page archive run comparison');

    $data['web_page_archive_run_comparison_details']['cid'] = [
      'title' => $this->t('Comparison ID'),
      'help' => $this->t('The unique comparison ID.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'sort' => ['id' => 'standard'],
    ];

    $data['web_page_archive_run_comparison_details']['url'] = [
      'title' => $this->t('Comparison URL'),
      'help' => $this->t('The URL being compared.'),
      'argument' => ['id' => 'string'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'string'],
      'sort' => ['id' => 'standard'],
    ];
    $data['web_page_archive_run_comparison_details']['results'] = [
      'title' => $this->t('Comparison Results'),
      'help' => $this->t('Comparison results between two captures.'),
      'argument' => ['id' => 'standard'],
      'field' => ['id' => 'web_page_archive_serialized_comparison_results'],
      'filter' => ['id' => 'numeric'],
      'sort' => ['id' => 'standard'],
    ];
    $data['web_page_archive_run_comparison_details']['variance'] = [
      'title' => $this->t('Variance'),
      'help' => $this->t('Variance between two captures.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'web_page_archive_variance'],
      'filter' => ['id' => 'numeric'],
      'sort' => ['id' => 'standard'],
    ];
    $data['web_page_archive_run_comparison_details']['has_left'] = [
      'title' => $this->t('Has Left?'),
      'help' => $this->t('Boolean indicating whether or not the URL exists in the left portion of the comparison.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'boolean'],
      'sort' => ['id' => 'standard'],
    ];
    $data['web_page_archive_run_comparison_details']['has_right'] = [
      'title' => $this->t('Has Right?'),
      'help' => $this->t('Boolean indicating whether or not the URL exists in the left portion of the comparison.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'boolean'],
      'sort' => ['id' => 'standard'],
    ];

    // Link comparison details to capture results.
    $data['web_page_archive_run_comparison_details']['run1']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_comparison_details']['run1']['relationship']['base'] = 'web_page_archive_run_revision__field_captures';
    $data['web_page_archive_run_comparison_details']['run1']['relationship']['base field'] = 'revision_id';
    $data['web_page_archive_run_comparison_details']['run1']['relationship']['title'] = $this->t('Web Page Archive Run #1 Capture Results');
    $data['web_page_archive_run_comparison_details']['run1']['relationship']['label'] = $this->t('Run #1 capture results.');
    $data['web_page_archive_run_comparison_details']['run1']['relationship']['extra'] = [
      ['left_field' => 'has_left', 'value' => 1],
      ['field' => 'delta', 'left_field' => 'delta1'],
      ['field' => 'langcode', 'left_field' => 'langcode'],
    ];

    $data['web_page_archive_run_comparison_details']['run2']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_comparison_details']['run2']['relationship']['base'] = 'web_page_archive_run_revision__field_captures';
    $data['web_page_archive_run_comparison_details']['run2']['relationship']['base field'] = 'revision_id';
    $data['web_page_archive_run_comparison_details']['run2']['relationship']['title'] = $this->t('Web Page Archive Run #2 Capture Results');
    $data['web_page_archive_run_comparison_details']['run2']['relationship']['label'] = $this->t('Run #2 capture results.');
    $data['web_page_archive_run_comparison_details']['run2']['relationship']['extra'] = [
      ['left_field' => 'has_right', 'value' => 1],
      ['field' => 'delta', 'left_field' => 'delta2'],
      ['field' => 'langcode', 'left_field' => 'langcode'],
    ];

    // Setup relationship to comparison variance table.
    $data['web_page_archive_run_comparison_details']['cid']['relationship']['id'] = 'standard';
    $data['web_page_archive_run_comparison_details']['cid']['relationship']['base'] = 'web_page_archive_comparison_variance';
    $data['web_page_archive_run_comparison_details']['cid']['relationship']['base field'] = 'cid';
    $data['web_page_archive_run_comparison_details']['cid']['relationship']['title'] = $this->t('Web Page Archive Comparison Variance Details');
    $data['web_page_archive_run_comparison_details']['cid']['relationship']['label'] = $this->t('The web page archive comparison variance details.');
    $data['web_page_archive_run_comparison_details']['cid']['relationship']['extra'] = [
      ['field' => 'cid', 'left_field' => 'cid'],
    ];

    // Expose web_page_archive_comparison_variance table and fields to views.
    $data['web_page_archive_comparison_variance'] = [];
    $data['web_page_archive_comparison_variance']['table'] = [];
    $data['web_page_archive_comparison_variance']['table']['group'] = t('Web page archive run comparison variance data');

    $data['web_page_archive_comparison_variance']['cid'] = [
      'title' => $this->t('Comparison ID'),
      'help' => $this->t('The comparison ID.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'sort' => ['id' => 'standard'],
    ];

    $data['web_page_archive_comparison_variance']['response_index'] = [
      'title' => $this->t('Response Index'),
      'help' => $this->t('The response index.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'sort' => ['id' => 'standard'],
    ];

    $data['web_page_archive_comparison_variance']['plugin_id'] = [
      'title' => $this->t('Plugin ID'),
      'help' => $this->t('The compare response plugin ID.'),
      'argument' => ['id' => 'string'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'web_page_archive_compare_response_filter'],
      'sort' => ['id' => 'standard'],
    ];

    $data['web_page_archive_comparison_variance']['variance'] = [
      'title' => $this->t('Variance'),
      'help' => $this->t('Variance between two captures.'),
      'argument' => ['id' => 'numeric'],
      'field' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'sort' => ['id' => 'standard'],
    ];

    return $data;
  }

}
