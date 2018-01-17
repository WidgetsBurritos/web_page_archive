<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class ModalController.
 *
 *  Returns responses for capture response routes.
 *
 * @package Drupal\web_page_archive\Controller
 */
class ModalController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Helper function to retrieve a revision delta.
   */
  private function getRevisionDelta($web_page_archive_run_revision, $delta) {
    if (empty($web_page_archive_run_revision->field_captures->offsetGet($delta)->value)) {
      return NULL;
    }
    $response = unserialize($web_page_archive_run_revision->field_captures->offsetGet($delta)->value);
    if (empty($response['capture_response'])) {
      return NULL;
    }

    return $response;
  }

  /**
   * Render array for modal.
   */
  public function modalContent($web_page_archive_run_revision, $delta) {
    $response = $this->getRevisionDelta($web_page_archive_run_revision, $delta);
    if (!isset($response)) {
      return ['#markup' => $this->t('Invalid capture response.')];
    }
    return $response['capture_response']->renderable(['mode' => 'full']);
  }

  /**
   * Capture delta.
   */
  public function modalTitle($web_page_archive_run_revision, $delta) {
    $response = $this->getRevisionDelta($web_page_archive_run_revision, $delta);
    if (!isset($response)) {
      return $this->t('Error!');
    }
    return $response['capture_url'];
  }

  /**
   * Helper function to retrieve a comparison index.
   */
  private function getComparisonIndex($wpa_run_comparison, $index) {
    $results = $wpa_run_comparison->getResults();
    if (empty($results[$index]['results'])) {
      return NULL;
    }
    $unserialized = unserialize($results[$index]['results']);
    if (!isset($unserialized['compare_response'])) {
      return NULL;
    }
    return $unserialized['compare_response'];
  }

  /**
   * Render array for modal.
   */
  public function compareModalContent($wpa_run_comparison, $index) {
    $response = $this->getComparisonIndex($wpa_run_comparison, $index);
    if (!isset($response)) {
      return ['#markup' => $this->t('Invalid compare response.')];
    }
    $options = [
      'mode' => 'full',
      'run_comparison' => $wpa_run_comparison,
      'index' => $index,
    ];
    return $response->renderable($options);
  }

  /**
   * Title for comparison modal.
   */
  public function compareModalTitle($wpa_run_comparison, $index) {
    return "{$wpa_run_comparison->label()}: {$index}";
  }

}
