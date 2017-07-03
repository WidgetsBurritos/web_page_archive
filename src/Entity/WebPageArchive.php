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
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/development/web-page-archive/{web_page_archive}",
 *     "add-form" = "/admin/config/development/web-page-archive/add",
 *     "edit-form" = "/admin/config/development/web-page-archive/{web_page_archive}/edit",
 *     "delete-form" = "/admin/config/development/web-page-archive/{web_page_archive}/delete",
 *     "collection" = "/admin/config/development/web-page-archive"
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

}
