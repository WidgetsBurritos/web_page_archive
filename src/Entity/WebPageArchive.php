<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\web_page_archive\WebPageArchiveInterface;

/**
 * Defines the Web Page Archive entity.
 *
 * @ConfigEntityType(
 *   id = "web_page_archive",
 *   label = @Translation("Web Page Archive"),
 *   handlers = {
 *     "access" = "Drupal\web_page_archive\WebPageArchiveAccessController",
 *     "list_builder" = "Drupal\web_page_archive\Controller\WebPageArchiveListBuilder",
 *     "form" = {
 *       "add" = "Drupal\web_page_archive\Form\WebPageArchiveForm",
 *       "edit" = "Drupal\web_page_archive\Form\WebPageArchiveForm",
 *       "delete" = "Drupal\web_page_archive\Form\WebPageArchiveDeleteForm",
 *     }
 *   },
 *   config_prefix = "web_page_archive",
 *   admin_permission = "administer web page archive",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/development/web-page-archive/{web_page_archive}",
 *     "delete-form" = "/admin/config/development/web-page-archive/{web_page_archive}/delete",
 *     "enable" = "/admin/config/development/web-page-archive/{web_page_archive}/enable",
 *     "disable" = "/admin/config/development/web-page-archive/{web_page_archive}/disable",
 *     "collection" = "/admin/config/development/web-page-archive",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "schedule",
 *   },
 * )
 */
class WebPageArchive extends ConfigEntityBase implements WebPageArchiveInterface {

  /**
   * The Web Page Archive ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Web Page Archive label.
   *
   * @var string
   */
  public $label;

}
