<?php

namespace Drupal\web_page_archive\Form;

use Cron\CronExpression;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\web_page_archive\Controller\WebPageArchiveController;
use Drupal\web_page_archive\Parser\SitemapParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for web page archive add and edit forms.
 */
abstract class WebPageArchiveFormBase extends EntityForm {

  use MessengerTrait;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\image\WebPageArchiveInterface
   */
  protected $entity;

  /**
   * The web page archive entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $webPageArchiveStorage;

  /**
   * The sitemap parser service.
   *
   * @var \Drupal\web_page_archive\Parser\SitemapParser
   */
  protected $sitemapParser;

  /**
   * Constructs a base class for web page archive add and edit forms.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $web_page_archive_storage
   *   The web page archive entity storage.
   * @param \Drupal\web_page_archive\Parser\SitemapParser $sitemap_parser
   *   The sitemap parser service.
   */
  public function __construct(EntityStorageInterface $web_page_archive_storage, SitemapParser $sitemap_parser = NULL) {
    $this->webPageArchiveStorage = $web_page_archive_storage;
    $this->sitemapParser = $sitemap_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('web_page_archive'),
      $container->get('web_page_archive.parser.xml.sitemap')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // If we're missing dependencies, we shouldn't have any form fields.
    if (!WebPageArchiveController::checkDependencies()) {
      return [];
    }

    $config = $this->config('web_page_archive.settings');

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => !$this->entity->isNew() ? $this->entity->label() : $config->get('defaults.label'),
      '#description' => $this->t("Label for the Web page archive entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\web_page_archive\Entity\WebPageArchive::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    // TODO: Convert to checkbox after checkbox can work with form states.
    // @see https://www.drupal.org/node/994360
    $form['use_cron'] = [
      '#type' => 'select',
      '#title' => $this->t('Run capture job automatically.'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => !$this->entity->isNew() ? (int) $this->entity->getUseCron() : (int) $config->get('defaults.use_cron'),
    ];

    $use_cron_state = [['select[name="use_cron"]' => ['value' => '1']]];
    // TODO: Implement constraint.
    $form['cron_schedule'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Crontab schedule (relative to PHP's default timezone)"),
      '#description' => $this->t('Crontab format (see https://crontab.guru/)'),
      '#default_value' => !$this->entity->isNew() ? $this->entity->getCronSchedule() : $config->get('defaults.cron_schedule'),
      '#states' => [
        'visible' => $use_cron_state,
      ],
    ];

    if (CronExpression::isValidExpression($this->entity->getCronSchedule())) {
      $cron = CronExpression::factory($this->entity->getCronSchedule());
      $next_run = $this->t('Next run: @next_run', ['@next_run' => $cron->getNextRunDate()->format('Y-m-d @ g:ia T')]);
      $form['cron_schedule_next_run'] = [
        '#type' => 'container',
        '#markup' => $next_run,
        '#states' => [
          'visible' => $use_cron_state,
        ],
      ];
    }

    $form['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout (ms)'),
      '#description' => $this->t('Amount of time to wait between captures, in milliseconds.'),
      '#default_value' => !$this->entity->isNew() ? $this->entity->getTimeout() : $config->get('defaults.timeout'),
    ];

    $form['url_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Capture Type'),
      '#options' => [
        'url' => $this->t('URL'),
        'sitemap' => $this->t('Sitemap URL'),
      ],
      '#default_value' => !$this->entity->isNew() ? $this->entity->getUrlType() : $config->get('defaults.url_type'),
    ];

    $form['use_robots'] = [
      '#type' => 'select',
      '#title' => $this->t('Honor robots.txt restrictions.'),
      '#description' => $this->t('If checked, capture utility will respect robots.txt crawling rules.'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => !$this->entity->isNew() ? (int) $this->entity->getUseRobots() : (int) $config->get('defaults.use_robots'),
      '#states' => [
        'visible' => [
          ['select[name="url_type"]' => ['value' => 'url']],
          ['select[name="url_type"]' => ['value' => 'sitemap']],
        ],
      ],
    ];

    $form['user_agent'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Browser user agent'),
      '#description' => $this->t('Specify the browser user agent. e.g. "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36"'),
      '#default_value' => !$this->entity->isNew() ? $this->entity->getUserAgent() : $config->get('defaults.user_agent'),
    ];

    $form['urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs to Capture'),
      '#description' => $this->t('A list of urls to capture.'),
      '#default_value' => !$this->entity->isNew() ? $this->entity->getUrlsText() : $config->get('defaults.urls'),
      '#states' => [
        'visible' => [
          ['select[name="url_type"]' => ['value' => 'url']],
          ['select[name="url_type"]' => ['value' => 'sitemap']],
        ],
        'required' => [
          ['select[name="url_type"]' => ['value' => 'url']],
          ['select[name="url_type"]' => ['value' => 'sitemap']],
        ],
      ],
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url_type = $form_state->getValue('url_type');

    // Validate URLs.
    if (in_array($url_type, ['sitemap', 'url'])) {
      $urls = explode(PHP_EOL, $form_state->getValue('urls'));

      foreach ($urls as $url) {
        $url = trim($url);
        if (!UrlHelper::isValid($url, TRUE)) {
          $form_state->setErrorByName('urls', $this->t('Invalid URL: @url', ['@url' => $url]));
        }
        elseif ($url_type == 'sitemap') {
          try {
            if (!isset($this->sitemapParser)) {
              throw new \Exception($this->t('Sitemap parser service could not be found'));
            }
            $parsed_urls = $this->sitemapParser->parse($url);
            if (empty($parsed_urls)) {
              throw new \Exception($this->t('No urls parsed'));
            }
          }
          catch (\Exception $e) {
            $form_state->setErrorByName('urls', $this->t('Invalid or Empty Sitemap: @url', ['@url' => $url]));
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->urlInfo('edit-form'));
  }

}
