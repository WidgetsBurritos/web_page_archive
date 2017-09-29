<?php

namespace Drupal\web_page_archive\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\web_page_archive\Entity\WebPageArchiveRunInterface;

/**
 * Class WebPageArchiveRunController.
 *
 *  Returns responses for Web page archive run routes.
 *
 * @package Drupal\web_page_archive\Controller
 */
class WebPageArchiveRunController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Web page archive run  revision.
   *
   * @param int $web_page_archive_run_revision
   *   The Web page archive run  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($web_page_archive_run_revision) {
    $web_page_archive_run = $this->entityTypeManager()->getStorage('web_page_archive_run')->loadRevision($web_page_archive_run_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('web_page_archive_run');

    return $view_builder->view($web_page_archive_run);
  }

  /**
   * Page title callback for a Web page archive run  revision.
   *
   * @param int $web_page_archive_run_revision
   *   The Web page archive run  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($web_page_archive_run_revision) {
    $web_page_archive_run = $this->entityTypeManager()->getStorage('web_page_archive_run')->loadRevision($web_page_archive_run_revision);
    return $this->t('Revision of %title from %date', ['%title' => $web_page_archive_run->label(), '%date' => format_date($web_page_archive_run->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Web page archive run .
   *
   * @param \Drupal\web_page_archive\Entity\WebPageArchiveRunInterface $web_page_archive_run
   *   A Web page archive run  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(WebPageArchiveRunInterface $web_page_archive_run) {
    $account = $this->currentUser();
    $langcode = $web_page_archive_run->language()->getId();
    $langname = $web_page_archive_run->language()->getName();
    $languages = $web_page_archive_run->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $web_page_archive_run_storage = $this->entityTypeManager()->getStorage('web_page_archive_run');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $web_page_archive_run->label()]) : $this->t('Revisions for %title', ['%title' => $web_page_archive_run->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $delete_permission = (($account->hasPermission("delete all web page archive run revisions") || $account->hasPermission('administer web page archive run entities')));

    $rows = [];

    $vids = $web_page_archive_run_storage->revisionIds($web_page_archive_run);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\web_page_archive\WebPageArchiveRunInterface $revision */
      $revision = $web_page_archive_run_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $web_page_archive_run->getRevisionId()) {
          $link = $this->l($date, new Url('entity.web_page_archive_run.revision', ['web_page_archive_run' => $web_page_archive_run->id(), 'web_page_archive_run_revision' => $vid]));
        }
        else {
          $link = $web_page_archive_run->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.web_page_archive_run.revision_delete', ['web_page_archive_run' => $web_page_archive_run->id(), 'web_page_archive_run_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['web_page_archive_run_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
