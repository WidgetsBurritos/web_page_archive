<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WebPageArchiveController.
 *
 * @package Drupal\web_page_archive\Controller
 */
class LockController extends ControllerBase {

  /**
   * Toggles the retention lock setting for the specified run.
   */
  public function toggleRetentionLock($web_page_archive_run_revision) {
    $locked = $web_page_archive_run_revision->getRetentionLocked();
    $web_page_archive_run_revision->setRetentionLocked(!$locked);
    $web_page_archive_run_revision->save();
    $wpa = $web_page_archive_run_revision->getConfigEntity();
    return $this->redirect('entity.web_page_archive.canonical', ['web_page_archive' => $wpa->id()]);
  }

}
