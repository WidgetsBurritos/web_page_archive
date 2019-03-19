<?php

namespace Drupal\wpa_screenshot_capture\Plugin\CaptureUtility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityBase;
use Drupal\wpa_screenshot_capture\Plugin\CaptureResponse\ScreenshotCaptureResponse;
use Screen\Capture;
use Screen\Image\Types;
use Spatie\Browsershot\Browsershot;

/**
 * Captures screenshot of a remote uri.
 *
 * @CaptureUtility(
 *   id = "wpa_screenshot_capture",
 *   label = @Translation("Screenshot capture utility", context = "Web Page Archive"),
 *   description = @Translation("Captures snapshot images for given URL", context = "Web Page Archive")
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
    // Handle missing URLs.
    if (!isset($data['url'])) {
      throw new \Exception('Capture URL is required');
    }

    switch ($this->configuration['browser']) {
      case 'phantomjs':
        return $this->capturePhantomJs($data);

      case 'chrome':
        return $this->captureHeadlessChrome($data);
    }

    throw new \Exception("Invalid browser '{$this->configuration['browser']}' specified");
  }

  /**
   * Captures using phantomjs.
   */
  public function capturePhantomJs(array $data = []) {
    // Configure PhantomJS capture tool.
    $screenCapture = new Capture($data['url']);
    $config = \Drupal::configFactory()->get('web_page_archive.wpa_screenshot_capture.settings');
    $screenCapture->binPath = dirname($config->get('system.phantomjs_path')) . '/';
    if (!file_exists($screenCapture->binPath . 'phantomjs')) {
      throw new \Exception("phantomjs could not be found at {$screenCapture->binPath}phantomjs");
    }
    $screenCapture->setWidth((int) $this->configuration['width']);
    if (!empty($this->configuration['background_color'])) {
      $screenCapture->setBackgroundColor($this->configuration['background_color']);
    }
    $screenCapture->setImageType($this->configuration['image_type']);
    if (!empty($data['user_agent'])) {
      $screenCapture->setUserAgentString($data['user_agent']);
    }
    $screenCapture->setDelay($this->configuration['delay']);
    $filename = $this->getFileName($data, $this->configuration['image_type']);

    // Save screenshot and set our response.
    $screenCapture->save(\Drupal::service('file_system')->realpath($filename));
    $this->response = new ScreenshotCaptureResponse($filename, $data['url']);

    return $this;
  }

  /**
   * Captures using headless chrome.
   */
  public function captureHeadlessChrome(array $data = []) {
    // Retrieve node/npm path.
    $config = \Drupal::configFactory();
    $file_system = \Drupal::service('file_system');
    $system_settings = $config->get('web_page_archive.settings')->get('system');
    $capture_utility_settings = $config->get('web_page_archive.wpa_screenshot_capture.settings')->get('system');

    if (!file_exists($system_settings['npm_path'])) {
      throw new \Exception("npm could not be found at '{$system_settings['npm_path']}'");
    }

    if (!file_exists($system_settings['node_path'])) {
      throw new \Exception("npm could not be found at '{$system_settings['node_path']}'");
    }

    // Capture screenshot.
    $filename = $this->getFileName($data, $this->configuration['image_type']);

    $screenCapture = Browsershot::url($data['url'])
      ->setNodeBinary($system_settings['node_path'])
      ->setNpmBinary($system_settings['npm_path'])
      ->fullPage()
      ->setOption('viewport.width', (int) $this->configuration['width']);

    $css = trim($this->configuration['css']);
    if (!empty($css)) {
      $screenCapture->setOption('addStyleTag', json_encode(['content' => $css]));
    }

    $greyscale = $this->configuration['greyscale'];
    if ($greyscale) {
      $screenCapture->greyscale();
    }

    $click = $this->configuration['click'];
    if (!empty($click)) {
      $screenCapture->click($click);
    }

    if (!empty($capture_utility_settings['node_modules_path'])) {
      $screenCapture->setNodeModulePath($capture_utility_settings['node_modules_path']);
    }

    if (!empty($capture_utility_settings['puppeteer_disable_sandbox'])) {
      $screenCapture->noSandbox();
    }

    if (!empty($this->configuration['delay'])) {
      $screenCapture->setDelay((int) $this->configuration['delay']);
    }

    // Add optional user agent.
    if (!empty($data['user_agent'])) {
      $screenCapture->userAgent($data['user_agent']);
    }

    // Save screenshot and set our response.
    $screenCapture->save($file_system->realpath($filename));
    $this->response = new ScreenshotCaptureResponse($filename, $data['url']);

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
    $browsershot_message = $this->t('Browsershot package missing.');
    $install_message = $this->t('The Web Page Archive module must be installed via composer.');

    $required_dependencies = [
      '\\Screen\\Capture' => "\\Screen\\Capture: {$capture_message} {$install_message}",
      '\\Spatie\\Browsershot\\Browsershot' => "\\Spatie\\Browsershot\\Browsershot: {$browsershot_message} {$install_message}",
    ];

    $missing_dependencies = [];
    foreach ($required_dependencies as $dependency => $message) {
      if (!class_exists($dependency)) {
        $missing_dependencies[$dependency] = $message;
      }
    }

    return $missing_dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = \Drupal::configFactory()->get('web_page_archive.wpa_screenshot_capture.settings');
    return [
      'browser' => $config->get('defaults.browser'),
      'width' => $config->get('defaults.width'),
      'delay' => $config->get('defaults.delay'),
      'background_color' => $config->get('defaults.background_color'),
      'image_type' => $config->get('defaults.image_type'),
      'css' => $config->get('defaults.css'),
      'greyscale' => $config->get('defaults.greyscale'),
      'click' => $config->get('defaults.click'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->get('web_page_archive.wpa_screenshot_capture.settings');

    $form['browser'] = [
      '#type' => 'select',
      '#title' => $this->t('Browser'),
      '#description' => $this->t('Specify the browser you would like to use to perform captures with.'),
      '#default_value' => $this->configuration['browser'],
      '#options' => [
        'chrome' => 'Headless Chrome',
        'phantomjs' => 'PhantomJS',
      ],
      '#required' => TRUE,
    ];

    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Capture width (in pixels)'),
      '#description' => $this->t('Specify the width you would like to capture.'),
      '#default_value' => $this->configuration['width'],
      '#required' => TRUE,
    ];
    $image_types = Types::available();
    $image_types = array_combine($image_types, $image_types);
    $form['image_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Image type'),
      '#options' => $image_types,
      '#empty_option' => $this->t('Select an image type'),
      '#default_value' => $this->configuration['image_type'],
      '#required' => TRUE,
    ];
    $form['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Browser background color'),
      '#description' => $this->t('Specify the browser background color in hexidecimal format. e.g. "#ffffff"'),
      '#default_value' => $this->configuration['background_color'],
      '#states' => [
        'visible' => [
          'select[name="data[browser]"]' => ['value' => 'phantomjs'],
        ],
      ],
    ];
    $form['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay (ms)'),
      '#description' => $this->t('How long to delay before capturing a screenshot. This is helpful if you need to wait for javascript or resources to load.'),
      '#default_value' => $this->configuration['delay'],
      '#required' => TRUE,
    ];
    $form['css'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS'),
      '#description' => $this->t('Additional CSS to apply prioring to capturing.'),
      '#default_value' => $this->configuration['css'],
      '#required' => FALSE,
      '#states' => [
        'visible' => [
          'select[name="data[browser]"]' => ['value' => 'chrome'],
        ],
      ],
    ];
    $form['greyscale'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Capture in Greyscale?'),
      '#description' => $this->t('If checked, images will be captured in greyscale, which can help minimize file size.'),
      '#default_value' => $this->configuration['greyscale'],
      '#states' => [
        'visible' => [
          'select[name="data[browser]"]' => ['value' => 'chrome'],
        ],
      ],
    ];
    $form['click'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Click an element on page.'),
      '#description' => $this->t('Add the css selector for an element on page, Useful for triggering javascript.'),
      '#default_value' => $this->configuration['click'],
      '#required' => FALSE,
      '#states' => [
        'visible' => [
          'select[name="data[browser]"]' => ['value' => 'chrome'],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $fields = [
      'browser',
      'width',
      'image_type',
      'background_color',
      'delay',
      'css',
      'greyscale',
      'click',
    ];

    foreach ($fields as $field) {
      $this->configuration[$field] = $form_state->getValue($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildSystemSettingsForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->get('web_page_archive.wpa_screenshot_capture.settings');

    $form['phantomjs_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PhantomJS path'),
      '#description' => $this->t('Full path to phantomjs binary on your system. (e.g. /usr/local/bin/phantomjs) *Important*: Due to package dependency binary must be called "phantomjs" or this will not work.'),
      '#default_value' => $config->get('system.phantomjs_path'),
    ];
    $form['magick_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ImageMagick path'),
      '#description' => $this->t('Full path to magick binary on your system. (e.g. /usr/local/bin/magick): Requires ImageMagick 7.'),
      '#default_value' => $config->get('system.magick_path'),
    ];
    $form['magick_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ImageMagick highlight color'),
      '#description' => $this->t('The color used to identify pixel discrepancies. (e.g. #fff000)'),
      '#default_value' => $config->get('system.magick_color'),
    ];
    $image_types = Types::available();
    $image_types = array_combine($image_types, $image_types);
    $form['magick_extension'] = [
      '#type' => 'select',
      '#title' => $this->t('ImageMagick extension'),
      '#description' => $this->t('Extension to use for pixel comparison images.'),
      '#options' => $image_types,
      '#empty_option' => $this->t('Select an image type'),
      '#default_value' => $config->get('system.magick_extension'),
      '#required' => TRUE,
    ];
    $url = 'https://github.com/spatie/browsershot#custom-node-module-path';
    $label = $this->t('more details');
    $node_modules_details_link = $this->getFormDescriptionLinkFromUrl($url, $label);
    $form['node_modules_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('node_modules path'),
      '#description' => $this->t('Path to /node_modules/ containing puppeteer. Leave empty if puppeteer is installed globally. See @details_link.', ['@details_link' => $node_modules_details_link]),
      '#default_value' => $config->get('system.node_modules_path'),
    ];
    $url = 'https://github.com/GoogleChrome/puppeteer/blob/e6725e15af6e883e83d4e7632765e276ca165f69/docs/troubleshooting.md#chrome-headless-fails-due-to-sandbox-issues';
    $label = $this->t('more details');
    $sandbox_details_link = $this->getFormDescriptionLinkFromUrl($url, $label);
    $form['puppeteer_disable_sandbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Disable puppeteer's sandbox mode."),
      '#description' => $this->t('Warning. This has security implications. See @details_link.', ['@details_link' => $sandbox_details_link]),
      '#default_value' => $config->get('system.puppeteer_disable_sandbox'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupRevision($revision_id) {
    ScreenshotCaptureResponse::cleanupRevision($revision_id);
  }

}
