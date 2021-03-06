<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
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

  use DependencySerializationTrait;
  use StringTranslationTrait;
  use FileStorageTrait;
  use NotifiableTrait;

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
  public function getFileName(array $data, $extension) {
    $entity_id = $data['run_entity']->getConfigEntity()->id();
    return $this->getUniqueFileName($entity_id, $data['run_uuid'], $data['url'], 'captures', $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function getNotificationContexts() {
    return [
      'capture_complete_single' => [
        'label' => $this->t('Single capture completion'),
        'description' => $this->t('This context occurs after a single capture is performed.'),
      ],
      'capture_complete_all' => [
        'label' => $this->t('Full capture completion'),
        'description' => $this->t('This context occurs after all captures are performed.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacementListByContext($context) {
    return [];
  }

}
