<?php

namespace Drupal\web_page_archive\Plugin;

/**
 * Base class for image comparison utility plugins.
 */
abstract class FilterableComparisonUtilityBase extends ComparisonUtilityBase {

  /**
   * {@inheritdoc}
   */
  public function isFilterable() {
    return TRUE;
  }

  /**
   * Retrieves filter criteria.
   */
  abstract public function getFilterCriteria();

}
