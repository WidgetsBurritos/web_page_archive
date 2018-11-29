<?php

namespace Drupal\web_page_archive\Component;

use Screen\Capture;
use Screen\Image\Types;
use Spatie\Browsershot\Browsershot;

/**
 * Standalone component for capturing via headless chrome.
 */
class HeadlessChromeCapture {

  /**
   * Constructs new HeadlessChromecapture instance.
   */
  public function __construct($node_path, $npm_path, $node_modules_path = '') {
    $this->settings = [
      'npm_path' => $npm_path,
      'node_path' => $node_path,
      'node_modules_path' => $node_modules_path,
    ];
  }

  /**
   * Retrieves default settings.
   */
  public function getDefaultSettings() {
    return [
      'puppeteer_disable_sandbox' => FALSE,
      'width' => 1280,
      'css' => '',
      'greyscale' => TRUE,
      'delay' => 0,
      'user_agent' => 'WPA',
    ];
  }

  /**
   * Retrieves a setting value.
   */
  public function getSetting($setting) {
    if (isset($this->settings[$setting])) {
      return $this->settings[$setting];
    }
    elseif (isset($this->getDefaultSettings()[$setting])) {
      return $this->getDefaultSettings()[$setting];
    }
    else {
      throw new \Exception("Unknown setting '{$setting}'");
    }
  }

  /**
   * Determines if a particular setting is not empty.
   */
  public function hasNonEmptySetting($setting) {
    return !empty($this->settings['setting']);
  }

  /**
   * Determines if a particular setting is set.
   */
  public function hasSetting($setting) {
    return isset($this->settings['setting']);
  }

  /**
   * Trims and sets css.
   */
  public function setCss($css) {
    $this->settings['css'] = trim($css);
  }

  /**
   * Sets delay (as integer).
   */
  public function setDelay($delay) {
    $this->settings['delay'] = (int) $delay;
  }

  /**
   * Sets image type.
   */
  public function setImageType($image_type) {
    $this->settings['image_type'] = $image_type;
  }

  /**
   * Sets greyscale (as boolean).
   */
  public function setGreyscale($greyscale) {
    $this->settings['greyscale'] = (boolean) $greyscale;
  }

  /**
   * Sets sandbox mode (as boolean).
   */
  public function setSandboxMode($sandbox_mode) {
    $this->settings['puppeteer_disable_sandbox'] = !$sandbox_mode;
  }

  /**
   * Sets user agent.
   */
  public function setUserAgent($user_agent) {
    $this->settings['user_agent'] = $user_agent;
  }

  /**
   * Sets capture width (as integer).
   */
  public function setWidth($width) {
    $this->settings['width'] = (int) $width;
  }

  /**
   * Performs a capture.
   */
  public function capture($url, $format) {
    $valid_formats = ['jpg', 'png', 'html', 'pdf'];
    if (!in_array($format, $valid_formats)) {
      throw new \Exception("Invalid format '{$format}'");
    }

    $npm_path = $this->getSetting('npm_path');
    $node_path = $this->getSetting('node_path');

    if (!file_exists($npm_path)) {
      throw new \Exception("npm could not be found at '{$npm_path}'");
    }

    if (!file_exists($node_path)) {
      throw new \Exception("node could not be found at '{$node_path}'");
    }

    $screenCapture = Browsershot::url($url)
      ->setNodeBinary($node_path)
      ->setNpmBinary($npm_path)
      ->fullPage()
      ->setOption('viewport.width', $this->getSetting('width'));

    if ($this->hasNonEmptySetting('css')) {
      $screenCapture->setOption('addStyleTag', json_encode(['content' => $css]));
    }

    if ($this->getSetting('greyscale')) {
      $screenCapture->greyscale();
    }

    if ($this->hasNonEmptySetting('node_modules_path')) {
      $screenCapture->setNodeModulePath($this->getSetting('node_modules_path'));
    }

    if ($this->getSetting('puppeteer_disable_sandbox')) {
      $screenCapture->noSandbox();
    }

    if ($this->hasNonEmptySetting('delay')) {
      $screenCapture->setDelay($this->getSetting('delay'));
    }

    // Add optional user agent.
    if ($this->hasNonEmptySetting('user_agent')) {
      $screenCapture->userAgent($this->getSetting('user_agent'));
    }

    if ($format == 'html') {
      return $screenCapture->bodyHtml();
    }

    // Generate temporary file.
    $tmp_file = tempnam(sys_get_temp_dir(), 'wpa');
die($tmp_file);
    $file = "{$tmp_file}.{$format}";
    $screenCapture->save($tmp_file);
    return $tmp_file;
  }

}
