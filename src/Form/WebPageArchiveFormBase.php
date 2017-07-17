<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for web page archive add and edit forms.
 */
abstract class WebPageArchiveFormBase extends EntityForm {

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
   * Constructs a base class for web page archive add and edit forms.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The web page archive entity storage.
   */
  public function __construct(EntityStorageInterface $image_style_storage) {
    $this->webPageArchiveStorage = $image_style_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('image_style')
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
    //
    // // TODO: Make plugins inject their form fields instead (future task).
    // $form['capture_screenshot'] = [
    //   '#type' => 'checkbox',
    //   '#title' => $this->t('Capture Screenshot?'),
    //   '#description' => $this->t('If checked, this job will include download and compare screenshots.'),
    //   '#default_value' => $web_page_archive->isScreenshotCapturing(),
    // ];
    //
    // $form['capture_html'] = [
    //   '#type' => 'checkbox',
    //   '#title' => $this->t('Capture HTML?'),
    //   '#description' => $this->t('If checked, this job will include download and compare html.'),
    //   '#default_value' => $web_page_archive->isHtmlCapturing(),
    // ];

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
