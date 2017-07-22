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
      $container->get('entity.manager')->getStorage('web_page_archive')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
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

    $form['sitemap_url'] = [
      '#type' => 'url',
      '#title' => $this->t('XML Sitemap URL'),
      '#description' => $this->t('Path to sitemap.'),
      '#required' => TRUE,
      '#default_value' => $this->entity->getSitemapUrl(),
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->urlInfo('edit-form'));
  }

}
