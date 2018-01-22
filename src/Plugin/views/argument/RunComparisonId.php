<?php

namespace Drupal\web_page_archive\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\NumericArgument;
use Drupal\web_page_archive\Entity\Sql\RunComparisonStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a web page archive run id.
 *
 * @ViewsArgument("wpa_cid")
 */
class RunComparisonId extends NumericArgument {

  /**
   * The run comparison storage.
   *
   * @var \Drupal\web_page_archive_run\Entity\Sql\RunComparisonStorageInterface
   */
  protected $runComparisonStorage;

  /**
   * Constructs the Nid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\web_page_archive_run\Entity\Sql\RunComparisonStorageInterface $run_comparison_storage
   *   The run comparison storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RunComparisonStorageInterface $run_comparison_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->runComparisonStorage = $run_comparison_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('wpa_run_comparison')
    );
  }

  /**
   * Override the behavior of title(). Get the title of the node.
   */
  public function titleQuery() {
    $titles = [];

    $run_comparisons = $this->runComparisonStorage->loadMultiple($this->value);
    foreach ($run_comparisons as $run_comparison) {
      $titles[] = $run_comparison->label();
    }
    return $titles;
  }

}
