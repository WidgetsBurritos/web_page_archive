<?php

namespace Drupal\web_page_archive\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Converts web_page_archive_run_revision parameter ids to revision entities.
 */
class WebPageArchiveRunRevisionParamConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    return \Drupal::entityTypeManager()->getStorage('web_page_archive_run')->loadRevision($value);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'web_page_archive_run_revision');
  }

}
