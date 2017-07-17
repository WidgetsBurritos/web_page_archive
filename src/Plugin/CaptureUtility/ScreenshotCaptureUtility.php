<?php

namespace Drupal\web_page_archive\Plugin\CaptureUtility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Entity\WebPageArchive;
use Drupal\web_page_archive\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Drupal\web_page_archive\Plugin\CaptureUtilityBase;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityBase;
use Screen\Capture;
use PhantomInstaller\PhantomBinary;

/**
 * Captures screenshot of a remote uri.
 *
 * @CaptureUtility(
 *   id = "screenshot_capture_utility",
 *   label = @Translation("Screenshot capture utility", context = "Web Page Archive"),
 * )
 */
class ScreenshotCaptureUtility extends ConfigurableCaptureUtilityBase {
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
   * {@inheritdoc}
   */
  public function missingDependencies() {
    $capture_message = $this->t('Screen Capture package missing.');
    $install_message = $this->t('The Web Page Archive module must be installed via composer.');
    $installer_message = $this->t('Phantom Installer package missing.');
    $binary_message = $this->t('PhantomJS binary missing. composer.json missing "post-install-cmd" command.');

    $required_dependencies = [
      '\\Screen\\Capture' => "\\Screen\\Capture: {$capture_message} {$install_message}",
      '\\PhantomInstaller\\Installer' => "\\PhantomInstaller\\Installer: {$installer_message} {$install_message}",
      '\\PhantomInstaller\\PhantomBinary' => "\\PhantomInstaller\\PhantomBinary: {$binary_message}",
    ];

    $missing_dependencies = [];
    foreach ($required_dependencies as $dependency => $message) {
      if (!class_exists($dependency)) {
        $missing_dependencies[$dependency] = $message;
      }
    }

    return $missing_dependencies;
  }

  // /**
  //  * {@inheritdoc}
  //  */
  // public function addConfigFormFields(array $form, WebPageArchive $web_page_archive = NULL) {
  //   // Default form options:
  //   $config = [
  //     "{$this->pluginId}" => FALSE,
  //     "{$this->pluginId}_width" => 1280,
  //   ];
  //
  //   // Look for set values.
  //   if (isset($web_page_archive)) {
  //     $instance = $web_page_archive->hasCaptureUtilityInstance($this->pluginId);
  //     $config = $instance['config'];
  //   }
  //
  //   // Setup form fields.
  //   $form[$this->pluginId] = [
  //     '#type' => 'checkbox',
  //     '#title' => $this->t('Capture Screenshot?'),
  //     '#description' => $this->t('If checked, this job will include download and compare screenshots.'),
  //     // '#default_value' => $web_page_archive->isScreenshotCapturing(),
  //     '#default_value' => $config[$this->pluginId],
  //   ];
  //   $form["{$this->pluginId}_width"] = [
  //     '#type' => 'number',
  //     '#title' => $this->t('Capture width (in pixels)'),
  //     '#description' => $this->t('Specify the width you would like to capture.'),
  //     // '#default_value' => $web_page_archive->isScreenshotCapturing(),
  //     // '#default_value' => TRUE,
  //     '#default_value' => $config["{$this->pluginId}_width"],
  //   ];
  //
  //   return $form;
  // }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => $this->t('Some instructions go here.'),
    ];
    // $form['capture'] = [
    //   '#type' => 'checkbox',
    //   '#title' => $this->t('Capture Screenshot?'),
    //   '#description' => $this->t('If checked, this archive will download and compare screenshots.'),
    //   // '#default_value' => $web_page_archive->isScreenshotCapturing(),
    //   // '#default_value' => $config[$this->pluginId],
    //   // TODO: How to determine this?
    //   '#default_value' => TRUE,
    // ];
    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Capture width (in pixels)'),
      '#description' => $this->t('Specify the width you would like to capture.'),
      // TODO: Get this value...
      '#default_value' => 1280,
    ];
    return $form;
  }

}
