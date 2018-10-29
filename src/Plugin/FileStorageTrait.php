<?php

namespace Drupal\web_page_archive\Plugin;

/**
 * Trait for utility plugins that use file storage.
 */
trait FileStorageTrait {

  /**
   * Prepares and returns the storage path for the specified run uuid.
   *
   * @param string $entity_id
   *   Specific entity id to setup path from.
   * @param string $run_uuid
   *   Specific run uuid to setup path from.
   * @param string $directory
   *   Directory in which to store results.
   */
  public function storagePath($entity_id = NULL, $run_uuid = NULL, $directory = NULL) {
    // TODO: Use custom stream wrapper.
    // @see https://www.drupal.org/node/2901781
    $scheme = file_default_scheme();
    $path_tokens = ["{$scheme}:/", 'web-page-archive'];
    if (isset($directory)) {
      $path_tokens[] = $directory;
    }
    if (isset($this->pluginDefinition['id'])) {
      $path_tokens[] = $this->pluginDefinition['id'];
    }
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

  /**
   * Retrieves a filename based on the specified data.
   *
   * @param string $entity_id
   *   Entity ID.
   * @param string $uuid
   *   Run UUID.
   * @param string $basename
   *   File base name.
   * @param string $directory
   *   File directory.
   * @param string $extension
   *   File extension.
   * @param int $index
   *   Index of file (to prevent duplicate files).
   *
   * @return string
   *   Retrieves a filename for a capture.
   */
  public function getUniqueFileName($entity_id, $uuid, $basename, $directory, $extension, $index = 1) {
    $file_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($basename));
    $file_name .= "-{$index}.{$extension}";
    $file_path = $this->storagePath($entity_id, $uuid, $directory) . '/' . $file_name;

    // If file exists update our index and try again.
    if (file_exists($file_path)) {
      return $this->getUniqueFileName($entity_id, $uuid, $basename, $directory, $extension, $index + 1);
    }

    return $file_path;
  }

}
