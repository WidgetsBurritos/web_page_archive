<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Plugin\PluginBase;
use Drupal\web_page_archive\Controller\CleanupController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Capture utility plugins.
 */
abstract class CaptureUtilityBase extends PluginBase implements CaptureUtilityInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The capture utility ID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The weight of the capture utility.
   *
   * @var int|string
   */
  protected $weight = '';

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('web_page_archive')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return [
      '#markup' => '',
      '#capture_utility' => [
        'id' => $this->pluginDefinition['id'],
        'label' => $this->label(),
        'description' => $this->pluginDefinition['description'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid() {
    return $this->uuid;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'uuid' => $this->getUuid(),
      'id' => $this->getPluginId(),
      'weight' => $this->getWeight(),
      'data' => $this->configuration,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration += [
      'data' => [],
      'uuid' => '',
      'weight' => '',
    ];
    $this->configuration = $configuration['data'] + $this->defaultConfiguration();
    $this->uuid = $configuration['uuid'];
    $this->weight = $configuration['weight'];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function missingDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupEntity($entity_id) {
    $path = $this->storagePath($entity_id);
    CleanupController::queueDirectoryDelete($path);
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupRevision($revision_id) {}

  /**
   * {@inheritdoc}
   */
  public function storagePath($entity_id = NULL, $run_uuid = NULL) {
    // TODO: Use custom stream wrapper.
    // @see https://www.drupal.org/node/2901781
    $scheme = file_default_scheme();
    $utility = $this->pluginDefinition['id'];
    $path_tokens = [
      "{$scheme}:/",
      'web-page-archive',
      $utility,
    ];
    if (isset($entity_id)) {
      $path_tokens[] = $entity_id;
    }
    if (isset($run_uuid)) {
      $path_tokens[] = $run_uuid;
    }
    $path = implode('/', $path_tokens);
    if (!file_prepare_directory($path, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      throw new \Exception("Could not write to $path");
    }
    return $path;
  }

}
