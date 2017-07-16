<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveForm extends EntityForm {

  /**
   * Plugin manager interface.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.capture_utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $web_page_archive = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $web_page_archive->label(),
      '#description' => $this->t("Label for the Web page archive entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $web_page_archive->id(),
      '#machine_name' => [
        'exists' => '\Drupal\web_page_archive\Entity\WebPageArchive::load',
      ],
      '#disabled' => !$web_page_archive->isNew(),
    ];

    $form['sitemap_url'] = [
      '#type' => 'url',
      '#title' => $this->t('XML Sitemap URL'),
      '#description' => $this->t('Path to sitemap.'),
      '#required' => TRUE,
      '#default_value' => $web_page_archive->getSitemapUrl(),
    ];

    $form['cron_schedule'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cron Schedule'),
      '#description' => $this->t('Not yet implemented.Schedule for the archiving. Uses CRON expressions - See: <a href="https://en.wikipedia.org/wiki/Cron#CRON_expression">https://en.wikipedia.org/wiki/Cron#CRON_expression</a>'),
      '#maxlength' => 255,
      '#default_value' => $web_page_archive->getCronSchedule(),
      '#disabled' => TRUE,
    ];

    // Add plugin fields.
    $pm = $this->pluginManager;
    foreach ($pm->getDefinitions() as $definition) {
      $plugin_instance = $pm->createInstance($definition['id']);
      $form = array_merge($form, $plugin_instance->addConfigFormFields($form, $web_page_archive));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $web_page_archive = $this->entity;

    // TODO: Validate XML feed URL? (Future task)
    $status = $web_page_archive->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Web page archive entity.', [
          '%label' => $web_page_archive->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Web page archive entity.', [
          '%label' => $web_page_archive->label(),
        ]));
    }
    $form_state->setRedirectUrl($web_page_archive->toUrl('collection'));
  }

}
