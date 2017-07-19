<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtilityBase;
use Screen\Capture;
use PhantomInstaller\PhantomBinary;

/**
 * Captures screenshot of a remote uri.
 *
 * @CaptureUtility(
 *   id = "ScreenshotCaptureUtility",
 *   label = @Translation("Screenshot capture utility", context = "Web Page Archive"),
 * )
 */
class ScreenshotCaptureUtility extends CaptureUtilityBase {
  /**
   * Most recent response.
   *
   * @var string|null
   */
  private $response = NULL;

  /**
   * {@inheritdoc}
   */
  public function capture(array $data = []) {
    $url = $data['url'];

    $screenCapture = new Capture($url);
    $screenCapture->binPath = PhantomBinary::getDir() . '/';
    // TODO: Convert these options to config settings.
    // @see https://www.drupal.org/node/2894732
    $screenCapture->setWidth(1200);
    $screenCapture->setClipWidth(1200);
    $screenCapture->setBackgroundColor('#ffffff');
    $screenCapture->setImageType('png');

    $file_path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $save_dir = "{$file_path}/screenshots/{$data['web_page_archive']->id()}/{$data['run_uuid']}";
    $file_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($url));
    $file_location = "{$save_dir}/{$file_name}";
    $screenCapture->save($file_location);
    $this->response = new ScreenshotCaptureResponse($screenCapture->getImageLocation());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Determines whether or not dependencies are missing.
   *
   * @return array
   *   Array containing missing dependencies.
   */
  public static function missingDependencies() {
    $required_dependencies = [
      '\\Screen\\Capture',
      '\\PhantomInstaller\\Installer',
      '\\PhantomInstaller\\PhantomBinary',
    ];
    $missing_dependencies = [];
    foreach ($required_dependencies as $dependency) {
      if (!class_exists($dependency)) {
        $missing_dependencies[] = $dependency;
      }
    }

    return $missing_dependencies;
  }

}
