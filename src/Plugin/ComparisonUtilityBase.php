<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for image comparison utility plugins.
 */
abstract class ComparisonUtilityBase extends PluginBase implements ComparisonUtilityInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use FileStorageTrait;

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
   * The compare response factory service.
   *
   * @var \Drupal\web_page_archive\Plugin\CompareResponseFactory
   */
  protected $compareResponseFactory;

  /**
   * The configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CompareResponseFactory $compare_response_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
    $this->compareResponseFactory = $compare_response_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('web_page_archive.compare.response')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return [
      '#markup' => '',
      '#comparison_utility' => [
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
  public function isAvailable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFilterable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable($tag) {
    return in_array($tag, $this->getPluginDefinition()['tags']);
  }

  /**
   * {@inheritdoc}
   */
  public function getFileName(array $data, $extension) {
    return $this->getUniqueFileName($data['run_comparison']->id(), NULL, $data['url'], 'comparisons', $extension);
  }

}
