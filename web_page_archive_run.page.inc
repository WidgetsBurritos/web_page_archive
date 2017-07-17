<?php

/**
 * @file
 * Contains web_page_archive_run.page.inc.
 *
 * Page callback for Web page archive run entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Web page archive run templates.
 *
 * Default template: web_page_archive_run.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_web_page_archive_run(array &$variables) {
  // Fetch WebPageArchiveRun Entity Object.
  $web_page_archive_run = $variables['elements']['#web_page_archive_run'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
