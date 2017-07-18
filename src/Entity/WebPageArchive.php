<?php

namespace Drupal\web_page_archive\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultLazyPluginCollection;
use Drupal\web_page_archive\Parser\SitemapParser;
use Drupal\web_page_archive\Plugin\CaptureUtilityInterface;
use GuzzleHttp\HandlerStack;

/**
 * Defines the Web Page Archive entity.
 *
 * @ConfigEntityType(
 *   id = "web_page_archive",
 *   label = @Translation("Web Page Archive"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\web_page_archive\Entity\WebPageArchiveListBuilder",
 *     "form" = {
 *       "add" = "Drupal\web_page_archive\Form\WebPageArchiveForm",
 *       "edit" = "Drupal\web_page_archive\Form\WebPageArchiveForm",
 *       "delete" = "Drupal\web_page_archive\Form\WebPageArchiveDeleteForm",
 *       "queue" = "Drupal\web_page_archive\Form\WebPageArchiveQueueForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\web_page_archive\Entity\Routing\WebPageArchiveHtmlRouteProvider",
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
 *     "canonical" = "/admin/config/system/web-page-archive/{web_page_archive}",
 *     "add-form" = "/admin/config/system/web-page-archive/add",
 *     "edit-form" = "/admin/config/system/web-page-archive/{web_page_archive}/edit",
 *     "delete-form" = "/admin/config/system/web-page-archive/{web_page_archive}/delete",
 *     "queue-form" = "/admin/config/system/web-page-archive/{web_page_archive}/queue",
 *     "collection" = "/admin/config/system/web-page-archive"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "sitemap_url",
 *     "cron_schedule",
 *     "capture_screenshot",
 *     "capture_html",
 *     "capture_utilities",
 *     "entity_run"
 *   }
 * )
 */
class WebPageArchive extends ConfigEntityBase implements WebPageArchiveInterface, EntityWithPluginCollectionInterface {

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
   * The array of capture utilities for this archive.
   *
   * @var array
   */
  protected $capture_utilities = [];

  /**
   * Holds the collection of capture utilities that are used by this archive.
   *
   * @var \Drupal\Core\Plugin\DefaultLazyPluginCollection
   */
  protected $capture_utility_collection;

  /**
   * Holds uuid for entity run.
   *
   * @var array
   */
  protected $entity_run = NULL;

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

  /**
   * {@inheritdoc}
   */
  public function getCaptureUtility($capture_utility) {
    return $this->getCaptureUtilities()->get($capture_utility);
  }

  /**
   * {@inheritdoc}
   */
  public function getCaptureUtilities() {
    if (!$this->capture_utility_collection) {
      $this->capture_utility_collection = new DefaultLazyPluginCollection($this->captureUtilityPluginManager(), $this->capture_utilities);
    }
    return $this->capture_utility_collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['capture_utilities' => $this->getCaptureUtilities()];
  }

  /**
   * {@inheritdoc}
   */
  public function addCaptureUtility(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getCaptureUtilities()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteCaptureUtility(CaptureUtilityInterface $capture_utility) {
    $this->getCaptureUtilities()->removeInstanceId($capture_utility->getUuid());
    return $this;
  }

  /**
   * Determines if entity has an instance of the specified plugin id.
   *
   * @param string $id
   *   Capture utility plugin id.
   */
  public function hasCaptureUtilityInstance($id) {
    foreach ($this->capture_utilities as $utility) {
      if ($utility['id'] == $id) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Deletes a capture utility by id.
   *
   * @param string $id
   *   Capture utility plugin id.
   */
  public function deleteCaptureUtilityById($id) {
    foreach ($this->capture_utilities as $utility) {
      if ($utility['id'] == $id) {
        $this->getCaptureUtilities()->removeInstanceId($utility['uuid']);
      }
    }
    return $this;
  }

  /**
   * Retrieves count of number of jobs in queue.
   *
   * @var int
   */
  public function getQueueCt() {
    $queue = $this->getQueue();
    return (isset($queue)) ? $queue->numberOfItems() : 0;
  }

  /**
   * Retrieves count of number of completed runs.
   *
   * @var int
   */
  public function getRunCt() {
    // TODO: Implement this.
    return 0;
  }

  /**
   * Retrieves the queue for the archive.
   *
   * @return \Drupal\Core\Queue\QueueInterface
   *   Queue object for this particular archive.
   */
  public function getQueue() {
    return \Drupal::service('queue')->get("web_page_archive_capture.{$this->uuid()}");
  }

  /**
   * Queues the archive to run.
   */
  public function startNewRun(HandlerStack $handler = NULL) {
    try {
      // Retrieve sitemap contents.
      // TODO: Move functionality into controller?
      $sitemap_parser = new SitemapParser($handler);
      $urls = $sitemap_parser->parse($this->getSitemapUrl());
      $queue = $this->getQueue();
      $run_uuid = $this->uuidGenerator()->generate();
      $run_entity = $this->getRunEntity();

      foreach ($urls as $url) {
        foreach ($this->getCaptureUtilities() as $utility) {
          $item = [
            'web_page_archive' => $this,
            'utility' => $utility,
            'url' => $url,
            'run_uuid' => $run_uuid,
            'run_entity' => $run_entity,
          ];
          $queue->createItem($item);
        }
      }

      $run_entity->setQueueCt($queue->numberOfItems());
      $run_entity->setNewRevision();
      $run_entity->setCapturedArray([]);
      $strings = [
        '@name' => $this->label(),
        '@uuid' => $run_uuid,
        '@queue_ct' => $queue->numberOfItems(),
      ];
      $run_entity->setRevisionLogMessage(t('Name: @name -- Run ID: @uuid -- Queue Ct: @queue_ct', $strings));
      $run_entity->save();
    }
    catch (\Exception $e) {
      // TODO: What to do here? (future task)
      drupal_set_message($e->getMessage(), 'warning');
    }
  }

  /**
   * Retrieves the run entity for this config entity.
   *
   * @return \Drupal\web_page_archive\Entity\WebPageArchiveRun
   */
  public function getRunEntity() {
    $entity = NULL;
    if (isset($this->entity_run)) {
      $entity = $this->entityRepository()->loadEntityByUuid('web_page_archive_run', $this->entity_run);
    }
    return $entity;
  }

  /**
   * Initializes run entity.
   */
  protected function initializeRunEntity() {
    $entity = NULL;

    if (!isset($entity)) {
      $data = [
        'uid' => \Drupal::currentUser()->id(),
        'name' => $this->label(),
        'uuid' => $this->uuidGenerator()->generate(),
        'status' => 0,
        'queue_ct' => 0,
        'config_entity' => $this->id(),
      ];
      $entity = $this->entityTypeManager()
        ->getStorage('web_page_archive_run')
        ->create($data);
      $entity->save();

      $this->entity_run = $data['uuid'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    // TODO: Handle this nonsense in plugins instead (future task).
    $plugin_options = [
      [
        'id' => 'HtmlCaptureUtility',
        'isCapturing' => $this->isHtmlCapturing(),
      ],
      [
        'id' => 'ScreenshotCaptureUtility',
        'isCapturing' => $this->isScreenshotCapturing(),
      ],
    ];

    foreach ($plugin_options as $option) {
      if (!$this->hasCaptureUtilityInstance($option['id']) && $option['isCapturing']) {
        $this->addCaptureUtility(['id' => $option['id']]);
      }
      elseif (!$option['isCapturing']) {
        $this->deleteCaptureUtilityById($option['id']);
      }
    }

    $this->initializeRunEntity();

    return parent::save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Delete run entity before deleting self.
    $this->getRunEntity()->delete();
    parent::delete();
  }

  /**
   * Wraps the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityRepositoryInterface
   *   A entity manager object.
   */
  protected function entityRepository() {
    return \Drupal::service('entity.repository');
  }

  /**
   * Wraps the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   A entity manager object.
   */
  protected function entityTypeManager() {
    return \Drupal::service('entity_type.manager');
  }

  /**
   * Wraps the search plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   A search plugin manager object.
   */
  protected function captureUtilityPluginManager() {
    return \Drupal::service('plugin.manager.capture_utility');
  }

  /**
   * Retrieves an editable config for this entity.
   *
   * @return \Drupal\Core\Config\Config
   *   A config object for the current entity.
   */
  protected function getEditableConfig() {
    return \Drupal::service('config.factory')->getEditable("web_page_archive.web_page_archive.{$this->id()}");
  }

}
