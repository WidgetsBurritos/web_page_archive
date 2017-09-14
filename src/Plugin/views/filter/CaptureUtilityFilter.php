<?php

namespace Drupal\web_page_archive\Plugin\views\filter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;

/**
 * My custom numeric filter.
 *
 * @ViewsFilter("web_page_archive_capture_utility_filter")
 */
class CaptureUtilityFilter extends InOperator {

  /**
   * Constructs a Bundle object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.capture_utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = [
      'contains' => [
        'title' => $this->t('Contains'),
        'short' => $this->t('contains'),
        'method' => 'opContains',
        'values' => 1,
      ],
    ];

    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  protected function operatorValues($values = 1) {
    $options = [];
    foreach ($this->operators() as $id => $info) {
      if (isset($info['values']) && $info['values'] == $values) {
        $options[] = $id;
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Make sure that the entity base table is in the query.
    $this->ensureMyTable();

    $field = "{$this->tableAlias}.{$this->realField}";

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opContains($field) {
    foreach ($this->value as $value) {
      $this->query->addWhere($this->options['group'], $field, '%' . Database::getConnection()->escapeLike(serialize($value)) . '%', 'LIKE');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $options = array_map(function ($option) {
        return $option['label'];
      }, $this->pluginManager->getDefinitions());
      asort($options);

      $this->valueOptions = $options;
    }

    return $this->valueOptions;
  }

}
