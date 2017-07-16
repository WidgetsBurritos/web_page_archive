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
 *     "canonical" = "/admin/config/development/web-page-archive/{web_page_archive}",
 *     "add-form" = "/admin/config/development/web-page-archive/add",
 *     "edit-form" = "/admin/config/development/web-page-archive/{web_page_archive}/edit",
 *     "delete-form" = "/admin/config/development/web-page-archive/{web_page_archive}/delete",
 *     "queue-form" = "/admin/config/development/web-page-archive/{web_page_archive}/queue",
 *     "collection" = "/admin/config/development/web-page-archive"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "sitemap_url",
 *     "cron_schedule",
 *     "capture_utilities",
 *     "runs"
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
   * Holds run data.
   *
   * @var array
   */
  protected $runs = [];

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
   *
   * @return string|boolean
   *   Returns uuid on success, false on failure.
   */
  public function hasCaptureUtilityInstance($id) {
    foreach ($this->capture_utilities as $utility) {
      if ($utility['id'] == $id) {
        return $utility;
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
   * Configures a capture utility by id.
   *
   * @param string $id
   *   Capture utility plugin id.
   */
  public function configureCaptureUtilityById($id, array $configuration) {
    $plugin_instance = $this->hasCaptureUtilityInstance($id);

    if ($plugin_instance) {
      $plugin_instance->setInstanceConfiguration($id, $configuration);
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

      foreach ($urls as $url) {
        foreach ($this->getCaptureUtilities() as $utility) {
          $item = [
            'web_page_archive' => $this,
            'utility' => $utility,
            'url' => $url,
            'run_uuid' => $run_uuid,
          ];
          $queue->createItem($item);
        }
      }

      $this->storeNewRun($run_uuid, $queue->numberOfItems());
    }
    catch (\Exception $e) {
      // TODO: What to do here? (future task)
      drupal_set_message($e->getMessage(), 'warning');
    }
  }

  /**
   * Stores run info into the database.
   */
  protected function storeNewRun($uuid, $queue_ct) {
    // TODO: Move functionality into controller?
    $config = $this->getEditableConfig();
    $new_run = [
      'uuid' => $uuid,
      'timestamp' => \Drupal::service('datetime.time')->getCurrentTime(),
      'queue_ct' => $queue_ct,
      'status' => 'pending',
      'captures' => [],
    ];

    $config->set("runs.{$uuid}", $new_run);
    $config->save();
  }

  /**
   * Marks a capture task complete.
   */
  public function markCaptureComplete($data) {
    // TODO: Move functionality into controller?
    $config = $this->getEditableConfig();
    $uuid = $this->uuidGenerator()->generate();
    $capture = [
      'uuid' => $uuid,
      'timestamp' => \Drupal::service('datetime.time')->getCurrentTime(),
      'status' => 'complete',
      'capture_url' => $data['url'],
      'capture_type' => $data['capture_response']->getType(),
      'content' => $data['capture_response']->getContent(),
    ];
    $config->set("runs.{$data['run_uuid']}.captures.{$uuid}", $capture);
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    // Retrieve field information from plugins.
    $pm = $this->captureUtilityPluginManager();
    foreach ($pm->getDefinitions() as $plugin_id => $definition) {
      // Assemble plugin configuration.
      $config = [];
      $plugin_instance = $pm->createInstance($plugin_id);
      $fields = $plugin_instance->getFormFields();
      foreach($fields as $field) {
        if (property_exists($this, $field)) {
          $config[$field] = $this->{$field};
        }
      }

      // Determine if this plugin is turned on or off.
      $isCapturing = property_exists($this, $plugin_id) && $this->{$plugin_id};
      $plugin_uuid = $this->hasCaptureUtilityInstance($plugin_id)['uuid'];

      // Add the utility.
      if (!$plugin_uuid && $isCapturing) {
        $this->addCaptureUtility(['id' => $plugin_id, 'config' => $config]);
      }
      // Delete the utility and it's configuration.
      elseif (!$isCapturing) {
        $this->deleteCaptureUtilityById($plugin_id);
      }
      // Update the utility's configuration.
      else {
        $full_config = $this->getCaptureUtilities()->getConfiguration()[$plugin_uuid];
        $full_config['config'] = $config;
        $this->getCaptureUtilities()->setInstanceConfiguration($plugin_uuid, $full_config);
      }
    }
    return parent::save();
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
