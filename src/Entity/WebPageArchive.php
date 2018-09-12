<?php

namespace Drupal\web_page_archive\Entity;

use Cron\CronExpression;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\DefaultLazyPluginCollection;
use Drupal\web_page_archive\Plugin\CaptureUtilityInterface;
use Drupal\web_page_archive\Controller\WebPageArchiveController;
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
 *       "add" = "Drupal\web_page_archive\Form\WebPageArchiveAddForm",
 *       "edit" = "Drupal\web_page_archive\Form\WebPageArchiveEditForm",
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
 *     "canonical" = "/admin/config/system/web-page-archive/jobs/{web_page_archive}",
 *     "add-form" = "/admin/config/system/web-page-archive/jobs/add",
 *     "edit-form" = "/admin/config/system/web-page-archive/jobs/{web_page_archive}/edit",
 *     "delete-form" = "/admin/config/system/web-page-archive/jobs/{web_page_archive}/delete",
 *     "queue-form" = "/admin/config/system/web-page-archive/jobs/{web_page_archive}/queue",
 *     "collection" = "/admin/config/system/web-page-archive"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "timeout",
 *     "url_type",
 *     "urls",
 *     "use_robots",
 *     "use_cron",
 *     "user_agent",
 *     "cron_schedule",
 *     "capture_utilities",
 *     "run_entity"
 *   }
 * )
 */
class WebPageArchive extends ConfigEntityBase implements WebPageArchiveInterface, EntityWithPluginCollectionInterface {

  use MessengerTrait;

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
   * URL type.
   *
   * @var int
   */
  protected $timeout;

  /**
   * URL type.
   *
   * @var string
   */
  protected $url_type;

  /**
   * URLs.
   *
   * @var string
   */
  protected $urls;

  /**
   * Whether or not to use cron.
   *
   * @var bool
   */
  protected $use_cron;

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
   * Holds uuid for entity run.
   *
   * @var array
   */
  protected $run_entity = NULL;

  /**
   * Retrieves capture timeout.
   */
  public function getTimeout() {
    return $this->timeout;
  }

  /**
   * Retrieves URL type.
   */
  public function getUrlType() {
    return $this->url_type;
  }

  /**
   * Retrieves URLs as text.
   */
  public function getUrlsText() {
    return trim($this->urls);
  }

  /**
   * Retrieves URLs as array.
   */
  public function getUrlList() {
    return array_map('trim', explode(PHP_EOL, $this->getUrlsText()));
  }

  /**
   * Indicates whether or not the config entity honors robots.txt restrictions.
   */
  public function getUseRobots() {
    return $this->use_robots;
  }

  /**
   * Indicates user agent used during crawling.
   */
  public function getUserAgent() {
    return $this->user_agent;
  }

  /**
   * Indicates whether or not a job is schedulable.
   */
  public function getUseCron() {
    return $this->use_cron;
  }

  /**
   * Retrieves the Cron schedule.
   */
  public function getCronSchedule() {
    return $this->getUseCron() ? $this->cron_schedule : NULL;
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
  public function getCaptureUtilityMap() {
    $ids = [];
    $definitions = $this->captureUtilityPluginManager()->getDefinitions();
    foreach ($this->capture_utilities as $utility) {
      $ids[$utility['id']] = $definitions[$utility['id']]['label'];
    }
    return $ids;
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
    $this->save();
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
    $query = \Drupal::database()->select('web_page_archive_run_revision', 'wpa_rr');
    $query->addExpression('COUNT(*)');
    $query->condition('id', $this->getRunEntity()->id());
    $ct = $query->execute()->fetchField();
    $run_before = $ct > 1 || !empty($this->getRunEntity()->getRevisionLogMessage());
    return $run_before ? $ct : 0;
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
   *
   * @return bool
   *   Indicates if run was successful or not.
   */
  public function startNewRun(HandlerStack $handler = NULL) {
    try {
      // Retrieve sitemap contents.
      $urls = $this->getUrlList();
      if ($this->getUrlType() == 'sitemap') {
        $parsed_urls = [];
        foreach ($urls as $url) {
          $parsed_urls = array_merge($parsed_urls, $this->sitemapParser($handler)->parse($url));
        }
        $urls = $parsed_urls;
      }

      if ($this->getUseRobots()) {
        $urls = $this->robotsValidator()->filterUrls($urls, $this->getUserAgent());
      }

      $queue = $this->getQueue();

      // Reset queue.
      $queue->deleteQueue();
      $queue->createQueue();

      $run_uuid = $this->uuidGenerator()->generate();
      $run_entity = $this->getRunEntity();
      $user_agent = $this->getUserAgent();

      foreach ($urls as $url) {
        foreach ($this->getCaptureUtilities() as $utility) {
          $item = [
            'web_page_archive' => $this,
            'utility' => $utility,
            'url' => $url,
            'run_uuid' => $run_uuid,
            'run_entity' => $run_entity,
            'user_agent' => $user_agent,
          ];
          $queue->createItem($item);
        }
      }

      if ($this->getRunCt() > 0) {
        $run_entity->setNewRevision();
      }
      $run_entity->setQueueCt($queue->numberOfItems());
      $run_entity->setCapturedArray([]);
      $run_entity->setCaptureUtilities($this->getCaptureUtilityMap());
      $run_entity->set('success_ct', 0);
      $run_entity->set('capture_size', 0);
      $strings = [
        '@name' => $this->label(),
        '@uuid' => $run_uuid,
        '@queue_ct' => $queue->numberOfItems(),
      ];
      $run_entity->setRevisionCreationTime(\Drupal::service('datetime.time')->getCurrentTime());
      $run_entity->setRevisionLogMessage(t('Name: @name -- Run ID: @uuid -- Queue Ct: @queue_ct', $strings));
      $run_entity->save();

      WebPageArchiveController::setBatch($this);

      return TRUE;
    }
    catch (\Exception $e) {
      // TODO: What to do here? (future task)
      $this->messenger()->addWarning($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Retrieves the run entity for this config entity.
   *
   * @return \Drupal\web_page_archive\Entity\WebPageArchiveRun
   *   Corresponding run content entity.
   */
  public function getRunEntity() {
    // Initialize run entity if doesn't already exist.
    if ($this->needsRunEntity()) {
      $this->initializeRunEntity();
    }

    // Retrieve run entity.
    if (isset($this->run_entity)) {
      $entity = $this->entityRepository()->loadEntityByUuid('web_page_archive_run', $this->run_entity);
      if (isset($entity)) {
        return $entity;
      }
    }

    throw new \Exception('Invalid run entity');
  }

  /**
   * Determines if the current config entity needs a run entity.
   */
  protected function needsRunEntity() {
    return !isset($this->run_entity) || !$this->entityRepository()->loadEntityByUuid('web_page_archive_run', $this->run_entity);
  }

  /**
   * Initializes run entity.
   */
  protected function initializeRunEntity() {
    $data = [
      'uid' => \Drupal::currentUser()->id(),
      'name' => $this->label(),
      'uuid' => $this->uuidGenerator()->generate(),
      'status' => 0,
      'queue_ct' => 0,
      'success_ct' => 0,
      'config_entity' => $this->id(),
    ];
    $entity = $this->entityTypeManager()
      ->getStorage('web_page_archive_run')
      ->create($data);
    $entity->save();

    $this->run_entity = $data['uuid'];
    $this->save();
  }

  /**
   * Calculates the next time the job will be run.
   */
  public function calculateNextRun() {
    if (!CronExpression::isValidExpression($this->getCronSchedule())) {
      throw new \Exception('Invalid crontab expression');
    }
    $cron = CronExpression::factory($this->getCronSchedule());
    return $cron->getNextRunDate()->format('U');
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    if ($this->getUseCron()) {
      $this->state()->set("web_page_archive.next_run.{$this->id()}", $this->calculateNextRun());
    }
    else {
      $this->state()->delete("web_page_archive.next_run.{$this->id()}");
    }

    return parent::save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Delete run entity before deleting self.
    if ($this->getRunEntity()) {
      $this->getRunEntity()->delete();
    }
    parent::delete();
  }

  /**
   * Wraps the sitemap parser.
   *
   * @return \Drupal\web_page_archive\Parser\SitemapParser
   *   A sitemap parser object.
   */
  protected function sitemapParser(HandlerStack $handler = NULL) {
    return \Drupal::service('web_page_archive.parser.xml.sitemap')->initializeConnection($handler);
  }

  /**
   * Wraps the robots.txt validator.
   *
   * @return \Drupal\web_page_archive\Validator\RobotsValidator
   *   A robots.txt validator object.
   */
  protected function robotsValidator(HandlerStack $handler = NULL) {
    return \Drupal::service('web_page_archive.validator.robots')->initializeConnection($handler);
  }

  /**
   * Wraps the state storage service.
   *
   * @return \Drupal\Core\State\StateInterface
   *   A state storage object.
   */
  protected function state() {
    return \Drupal::service('state');
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
