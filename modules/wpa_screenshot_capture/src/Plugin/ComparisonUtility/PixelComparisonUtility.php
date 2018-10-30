<?php

namespace Drupal\wpa_screenshot_capture\Plugin\ComparisonUtility;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\File\FileSystem;
use Drupal\web_page_archive\Plugin\CaptureResponseInterface;
use Drupal\web_page_archive\Plugin\CompareResponseFactory;
use Drupal\web_page_archive\Plugin\FilterableComparisonUtilityBase;
use Drupal\wpa_screenshot_capture\Plugin\CompareResponse\PixelScreenshotVarianceCompareResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Captures screenshot of a remote uri.
 *
 * @ComparisonUtility(
 *   id = "wpa_screenshot_capture_pixel_compare",
 *   label = @Translation("Screenshot: Pixel", context = "Web Page Archive"),
 *   description = @Translation("Compares images and generates diff images.", context = "Web Page Archive"),
 *   tags = {"screenshot"}
 * )
 */
class PixelComparisonUtility extends FilterableComparisonUtilityBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CompareResponseFactory $compare_response_factory, FileSystem $file_system, ConfigFactory $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $compare_response_factory);
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('web_page_archive.compare.response'),
      $container->get('file_system'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function compare(CaptureResponseInterface $a, CaptureResponseInterface $b, array $data = []) {
    // If service unavailable, return a -1 variance.
    if (!$this->isAvailable()) {
      return new PixelScreenshotVarianceCompareResponse(-1, '');
    }

    $settings = $this->configFactory->get('web_page_archive.wpa_screenshot_capture.settings');
    $file1 = $this->fileSystem->realpath($a->getContent());
    $file2 = $this->fileSystem->realpath($b->getContent());
    $file_path = $this->getFileName($data, $settings->get('system.magick_extension'));
    $outfile = $this->fileSystem->realpath($file_path);

    $process = new Process([
      $settings->get('system.magick_path'),
      'compare',
      '-highlight-color',
      $settings->get('system.magick_color'),
      '-metric',
      'AE',
      $file1,
      $file2,
      $outfile,
    ]);
    $process->run();

    $actual_errors = !$process->isSuccessful() ? (int) $process->getErrorOutput() : 0;

    $size = getimagesize($file1);
    $total_pixels = $size[0] * $size[1];
    $variance = 100 * $actual_errors / $total_pixels;

    return new PixelScreenshotVarianceCompareResponse($variance, $file_path);
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $settings = $this->configFactory->get('web_page_archive.wpa_screenshot_capture.settings');
    $magick = $settings->get('system.magick_path');
    if (empty($magick) || !file_exists($magick)) {
      return FALSE;
    }

    $process = new Process([$magick, '--version']);
    $process->run();
    return (substr($process->getOutput(), 0, 22) === 'Version: ImageMagick 7');
  }

  /**
   * {@inheritdoc}
   */
  public function getFilterCriteria() {
    return [
      PixelScreenshotVarianceCompareResponse::getId() => $this->label(),
    ];
  }

}
