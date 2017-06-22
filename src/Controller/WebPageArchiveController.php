<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\web_page_archive\WebPageArchiveInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for web page archive routes.
 */
class WebPageArchiveController extends ControllerBase {

  /**
   * Calls a method on a web page archive and reloads the listing page.
   *
   * @param \Drupal\web_page_archive\WebPageArchiveInterface $block
   *   The web page archive being acted upon.
   * @param string $op
   *   The operation to perform, e.g., 'enable' or 'disable'.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the listing page.
   */
  public function performOperation(WebPageArchiveInterface $archive, $op) {
    $archive->$op()->save();
    drupal_set_message($this->t('The web page archive settings have been updated.'));
    return $this->redirect('entity.web_page_archive.collection');
  }

}
