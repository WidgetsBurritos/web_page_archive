<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Web Page Archive entity.
 *
 * @ConfigEntityType(
 *   id = "web_page_archive",
 *   label = @Translation("Web Page Archive"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\web_page_archive\WebPageArchiveListBuilder",
 *     "form" = {
 *       "add" = "Drupal\web_page_archive\Form\WebPageArchiveForm",
 *       "edit" = "Drupal\web_page_archive\Form\WebPageArchiveForm",
 *       "delete" = "Drupal\web_page_archive\Form\WebPageArchiveDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\web_page_archive\WebPageArchiveHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "web_page_archive",
 *   admin_permission = "administer web page archive",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/development/web-page-archive/{web_page_archive}",
 *     "add-form" = "/admin/config/development/web-page-archive/add",
 *     "edit-form" = "/admin/config/development/web-page-archive/{web_page_archive}/edit",
 *     "delete-form" = "/admin/config/development/web-page-archive/{web_page_archive}/delete",
 *     "collection" = "/admin/config/development/web-page-archive"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "sitemap_url",
 *     "cron_schedule",
 *     "capture_screenshot",
 *     "capture_html"
 *   }
 * )
 */
class WebPageArchive extends ConfigEntityBase implements WebPageArchiveInterface {

  /**
   * The Web Page Archive ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Web Page Archive label.
   *
   * @var string
   */
  protected $label;

  /**
   * The XML sitemap URL.
   *
   * @var uri
   */
  protected $sitemap_url;

  /**
   * The cron schedule.
   *
   * @var string
   */
  protected $cron_schedule;

  /**
   * Boolean indicating if entity captures HTML.
   *
   * @var bool
   */
  protected $capture_html;

  /**
   * Boolean indicating if entity captures screenshot.
   *
   * @var bool
   */
  protected $capture_screenshot;

  /**
   * Retrieves the Sitemap URL.
   */
  public function getSitemapUrl() {
    return $this->sitemap_url;
  }

  /**
   * Retrieves the Cron schedule.
   */
  public function getCronSchedule() {
    return $this->cron_schedule;
  }

  /**
   * Retrieves whether or not entity captures HTML.
   */
  public function isHtmlCapturing() {
    return $this->capture_html;
  }

  /**
   * Returns whether or not entity captures screenshot.
   */
  public function isScreenshotCapturing() {
    return $this->capture_screenshot;
  }

}
