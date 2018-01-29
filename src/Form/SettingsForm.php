<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\web_page_archive\Controller\WebPageArchiveController;
use Drupal\web_page_archive\Plugin\CaptureUtilityManager;
use Drupal\web_page_archive\Plugin\ComparisonUtilityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure web page archive settings.
 */
class SettingsForm extends ConfigFormBase {

  protected $comparisonUtilityManager;

  /**
   * Constructs a base class for web page archive add and edit forms.
   *
   * @param \Drupal\web_page_archive\Plugin\CaptureUtilityManager $capture_utility_manager
   *   The capture utility manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\web_page_archive\Plugin\ComparisonUtilityManager $comparison_utility_manager
   *   The comparison utility manager service.
   */
  public function __construct(CaptureUtilityManager $capture_utility_manager, ConfigFactoryInterface $config_factory, ComparisonUtilityManager $comparison_utility_manager) {
    $this->captureUtilityManager = $capture_utility_manager;
    $this->configFactory = $config_factory;
    $this->comparisonUtilityManager = $comparison_utility_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.capture_utility'),
      $container->get('config.factory'),
      $container->get('plugin.manager.comparison_utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_page_archive_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'web_page_archive.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // If we're missing dependencies, we shouldn't have any form fields.
    if (!WebPageArchiveController::checkDependencies()) {
      return [];
    }

    // Attach form fields.
    $this->buildFormSystemSettings($form, $form_state);
    $this->buildFormCronSettings($form, $form_state);
    $this->buildFormDefaults($form, $form_state);
    $this->buildFormDefaultComparisonUtilitySettings($form, $form_state);
    $this->buildFormCaptureUtilityFields($form, $form_state);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->submitFormSettings($form, $form_state);
    $this->submitFormCaptureUtilitySettings($form, $form_state);
    parent::submitForm($form, $form_state);
  }

  /**
   * Attach module settings to the form array.
   */
  public function buildFormSystemSettings(array &$form, FormStateInterface $form_state) {
    $config = $this->config('web_page_archive.settings');

    $form['system'] = [
      '#type' => 'details',
      '#title' => $this->t('System Settings'),
      '#tree' => TRUE,
    ];

    $form['system']['node_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('NodeJS path'),
      '#description' => $this->t('Full path to NodeJS binary on your system. Requires node v7.6.0 or greater. (e.g. /usr/local/bin/node)'),
      '#default_value' => $config->get('system.node_path'),
    ];

    $form['system']['npm_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('NPM path'),
      '#description' => $this->t('Full path to npm binary on your system. Requires npm v4.1.2 or greater (e.g. /usr/local/bin/npm)'),
      '#default_value' => $config->get('system.npm_path'),
    ];
  }

  /**
   * Attach module settings to the form array.
   */
  public function buildFormCronSettings(array &$form, FormStateInterface $form_state) {
    $config = $this->config('web_page_archive.settings');

    $form['cron'] = [
      '#type' => 'details',
      '#title' => $this->t('Cron Settings'),
      '#tree' => TRUE,
    ];

    $form['cron']['capture_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Cron Capture Max'),
      '#description' => $this->t('Maximum number of captures to process per cron run.'),
      '#default_value' => $config->get('cron.capture_max'),
    ];

    $form['cron']['file_cleanup'] = [
      '#type' => 'number',
      '#title' => $this->t('Cron File Cleanup'),
      '#description' => $this->t('Maximum number of files marked for deletion to purge per cron run.'),
      '#default_value' => $config->get('cron.file_cleanup'),
    ];
  }

  /**
   * Attach comparison utility settings.
   */
  public function buildFormDefaultComparisonUtilitySettings(array &$form, FormStateInterface $form_state) {
    $config = $this->config('web_page_archive.settings');

    $form['comparison'] = [
      '#type' => 'details',
      '#title' => $this->t('Default Comparison Settings'),
      '#tree' => TRUE,
    ];
    $form['comparison']['run1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Run #1'),
      '#description' => $this->t('Specify the revision ID for the run you wish to use as your baseline.'),
      '#autocomplete_route_name' => 'web_page_archive.autocomplete.runs',
      '#default_value' => $config->get('comparison.run1'),
    ];
    $form['comparison']['run2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Run #2'),
      '#description' => $this->t('Specify the revision ID for the run you wish to compare against.'),
      '#autocomplete_route_name' => 'web_page_archive.autocomplete.runs',
      '#default_value' => $config->get('comparison.run2'),
    ];
    $form['comparison']['strip_type'] = [
      '#type' => 'select',
      '#title' => $this->t('URL/key stripping type'),
      '#options' => [
        '' => $this->t('None'),
        'string' => $this->t('String-based'),
        'regex' => $this->t('RegEx-based'),
      ],
      '#description' => $this->t('If comparing across hosts (e.g. www.mysite.com vs staging.mysite.com), you can strip portions of the URL or comparison key off.'),
      '#default_value' => $config->get('comparison.strip_type'),
    ];
    $form['comparison']['strip_patterns'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URL/key stripping patterns'),
      '#description' => $this->t('Enter pattern(s) you would like stripped from comparison key. One pattern per line.'),
      '#states' => [
        'visible' => [
          ['select[name="comparison[strip_type]"]' => ['value' => 'string']],
          ['select[name="comparison[strip_type]"]' => ['value' => 'regex']],
        ],
      ],
      '#default_value' => $config->get('comparison.strip_patterns') ?: [],
    ];

    $comparison_utilities = $this->comparisonUtilityManager->getDefinitions();
    $options = [];
    foreach ($comparison_utilities as $key => $comparison_utility) {
      $options[$key] = $comparison_utility['label'];
    }
    if (!empty($options)) {
      $form['comparison']['comparison_utilities'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Comparison Utilities'),
        '#description' => $this->t('Select which comparison utilities you want to use. If a particular comparison utility is not applicable to a specific capture it will be skipped.'),
        '#options' => $options,
        '#default_value' => $config->get('comparison.comparison_utilities') ?: [],
      ];
    }
  }

  /**
   * Attach default config fields to the form array.
   */
  public function buildFormDefaults(array &$form, FormStateInterface $form_state) {
    $config = $this->config('web_page_archive.settings');

    $form['defaults'] = [
      '#type' => 'details',
      '#title' => $this->t('Default Entity Settings'),
      '#tree' => TRUE,
    ];

    $form['defaults']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Label'),
      '#maxlength' => 255,
      '#default_value' => $config->get('defaults.label'),
      '#description' => $this->t("Label for the Web page archive entity."),
    ];

    // TODO: Convert to checkbox after checkbox can work with form states.
    // @see https://www.drupal.org/node/994360
    $form['defaults']['use_cron'] = [
      '#type' => 'select',
      '#title' => $this->t('Run capture job automatically by default?'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => $config->get('defaults.use_cron'),
    ];

    $use_cron_state = [['select[name="use_cron"]' => ['value' => '1']]];

    $form['defaults']['cron_schedule'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Default crontab schedule (relative to PHP's default timezone)"),
      '#description' => $this->t('Crontab format (see https://crontab.guru/)'),
      '#default_value' => $config->get('defaults.cron_schedule'),
      '#states' => [
        'visible' => $use_cron_state,
      ],
    ];

    $form['defaults']['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Default Timeout (ms)'),
      '#description' => $this->t('Amount of time to wait between captures, in milliseconds.'),
      '#default_value' => $config->get('defaults.timeout'),
    ];

    $form['defaults']['url_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Capture Type'),
      '#options' => [
        'url' => $this->t('URL'),
        'sitemap' => $this->t('Sitemap URL'),
      ],
      '#default_value' => $config->get('defaults.url_type'),
    ];

    $form['defaults']['use_robots'] = [
      '#type' => 'select',
      '#title' => $this->t('Honor robots.txt restrictions by default?'),
      '#description' => $this->t('If checked, capture utility will respect robots.txt crawling rules.'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => $config->get('defaults.use_robots'),
      '#states' => [
        'visible' => [
          ['select[name="url_type"]' => ['value' => 'url']],
          ['select[name="url_type"]' => ['value' => 'sitemap']],
        ],
      ],
    ];

    $form['defaults']['user_agent'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default browser user agent'),
      '#description' => $this->t('Specify the browser user agent. e.g. "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36"'),
      '#default_value' => $config->get('defaults.user_agent'),
    ];

    $form['defaults']['urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Default URLs to Capture'),
      '#description' => $this->t('A list of urls to capture.'),
      '#default_value' => $config->get('defaults.urls'),
      '#states' => [
        'visible' => [
          ['select[name="url_type"]' => ['value' => 'url']],
          ['select[name="url_type"]' => ['value' => 'sitemap']],
        ],
      ],
    ];
  }

  /**
   * Submit default entity settings.
   */
  public function submitFormSettings(array &$form, FormStateInterface $form_state) {
    $settings = \Drupal::configFactory()->getEditable('web_page_archive.settings');
    $groups = ['system', 'cron', 'defaults', 'comparison'];
    foreach ($groups as $group) {
      $defaults = $form_state->getValue($group);
      foreach ($defaults as $field => $value) {
        $settings->set("{$group}.{$field}", $value);
      }
    }
    $settings->save();
  }

  /**
   * Attach default capture utility config fields to the form array.
   */
  public function buildFormCaptureUtilityFields(array &$form, FormStateInterface $form_state) {
    // Define capture utility field groups.
    $groups = [
      [
        'id' => 'system',
        'label' => $this->t('System Settings'),
        'method' => 'buildSystemSettingsForm',
      ],
      [
        'id' => 'defaults',
        'label' => $this->t('Default Values'),
        'method' => 'buildConfigurationForm',
      ],
    ];

    // Iterate through each capture utility plugin searching for fields.
    $plugin_definitions = $this->captureUtilityManager->getDefinitions();
    foreach ($plugin_definitions as $plugin_definition) {
      // Initialize section.
      $config = $this->config("web_page_archive.{$plugin_definition['id']}.settings");
      $form[$plugin_definition['id']] = [
        '#type' => 'details',
        '#title' => $this->t('@label Settings', ['@label' => $plugin_definition['label']]),
        '#tree' => TRUE,
      ];

      // Loop through each group and attach necessary fields.
      foreach ($groups as $group) {
        $form[$plugin_definition['id']][$group['id']] = [
          '#type' => 'fieldset',
          '#title' => $group['label'],
        ];
        $subform_state = SubformState::createForSubform($form[$plugin_definition['id']][$group['id']], $form, $form_state);
        $capture_utility = $this->captureUtilityManager->createInstance($plugin_definition['id']);
        $form[$plugin_definition['id']][$group['id']] = $capture_utility->{$group['method']}($form[$plugin_definition['id']][$group['id']], $subform_state);

        // If group is empty, remove it. Otherwise set default values.
        if (empty($form[$plugin_definition['id']][$group['id']])) {
          unset($form[$plugin_definition['id']][$group['id']]);
        }
        else {
          $fields = $config->get($group['id']);
          foreach ($fields as $field => $value) {
            $form[$plugin_definition['id']][$group['id']][$field]['#default_value'] = $value;
            unset($form[$plugin_definition['id']][$group['id']][$field]['#required']);
          }
        }
      }
    }
  }

  /**
   * Submit capture utilty default settings.
   */
  public function submitFormCaptureUtilitySettings(array &$form, FormStateInterface $form_state) {
    $plugin_definitions = $this->captureUtilityManager->getDefinitions();
    foreach ($plugin_definitions as $plugin_definition) {
      $settings = $this->configFactory->getEditable("web_page_archive.{$plugin_definition['id']}.settings");
      $groups = ['defaults', 'system'];
      $fields = $form_state->getValue($plugin_definition['id']);
      foreach ($groups as $group) {
        if (empty($fields[$group])) {
          continue;
        }
        foreach ($fields[$group] as $field => $value) {
          $settings->set("{$group}.{$field}", $value);
        }
      }
      $settings->save();
    }
  }

}
