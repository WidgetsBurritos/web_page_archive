<?php

/**
 * @file
 * Post Update commands for wpa_screenshot_capture module.
 */

/**
 * Issue 3011372: Sets CSS value to empty string for existing config entities.
 */
function wpa_screenshot_capture_post_update_3011372_set_default_css_value() {
  _wpa_screenshot_capture_update_data_fields(['css' => '']);
}

/**
 * Issue 2922939: Sets greyscale value to false for existing config entities.
 */
function wpa_screenshot_capture_post_update_2922939_set_default_greyscale_value() {
  _wpa_screenshot_capture_update_data_fields(['greyscale' => FALSE]);
}

/**
 * Issue 3021730: Sets greyscale value to false for existing config entities.
 */
function wpa_screenshot_capture_post_update_3021730_set_default_greyscale_value() {
  _wpa_screenshot_capture_update_data_fields(['click' => '']);
}

/**
 * Helper function for updating the data array values in screenshot entities.
 */
function _wpa_screenshot_capture_update_data_fields(array $data = []) {
  $config_factory = \Drupal::configFactory();
  $config_prefix = 'web_page_archive.web_page_archive';
  $keys = $config_factory->listAll($config_prefix);

  foreach ($keys as $key) {
    $wpa_config = $config_factory->getEditable($key);

    $utilities = $wpa_config->get('capture_utilities');
    $changed = FALSE;

    // Search for screenshot capture utilities, remove clip_width and set delay.
    foreach ($utilities as $key => $utility) {
      if ($utilities[$key]['id'] == 'wpa_screenshot_capture') {
        if (!empty($data)) {
          foreach ($data as $data_key => $data_value) {
            $utilities[$key]['data'][$data_key] = $data_value;
          }
          $changed = TRUE;
        }
      }
    }

    // Update config entity if changed.
    if ($changed) {
      $wpa_config->set('capture_utilities', $utilities);
      $wpa_config->save();
    }
  }
}
