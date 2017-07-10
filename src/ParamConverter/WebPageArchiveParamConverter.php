<?php

namespace Drupal\web_page_archive\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Converts web_page_archive parameter ids to entities.
 */
class WebPageArchiveParamConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    return \Drupal::entityTypeManager()->getStorage('web_page_archive')->load($value);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'web_page_archive');
  }

}
