<?php

namespace Drupal\web_page_archive\Plugin\QueueWorker;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides functionality for running capture jobs.
 *
 * @QueueWorker(
 *   id = "web_page_archive_cleanup",
 *   title = @Translation("Web Page Archive Cleanup"),
 * )
 */
class FileCleanupQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * FileCleanupQueueWorker constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileSystemInterface $file_system = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (file_exists($data['path'])) {
      $logger = \Drupal::logger('web_page_archive');
      switch ($data['type']) {
        case 'file':
          if (\Drupal::service('file_system')->unlink($data['path'])) {
            $logger->notice(t('Deleted file @file', ['@file' => $data['path']]));
            return TRUE;
          }
          else {
            throw new \Exception(t('Could not delete @file', ['@file' => $data['path']]));
          }
        case 'directory':
          if ($this->fileSystem->deleteRecursive($data['path'])) {
            $logger->notice(t('Deleted directory: @dir', ['@dir' => $data['path']]));
            return TRUE;
          }
          else {
            throw new \Exception(t('Could not delete @dir', ['@dir' => $data['path']]));
          }
      }
    }

    return FALSE;
  }

}
