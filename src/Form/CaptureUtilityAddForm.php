<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\CaptureUtilityManager;
use Drupal\web_page_archive\Entity\WebPageArchiveInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an add form for capture utilities.
 */
class CaptureUtilityAddForm extends CaptureUtilityFormBase {

  /**
   * The capture utility manager.
   *
   * @var \Drupal\image\CaptureUtilityManager
   */
  protected $captureUtilityManager;

  /**
   * Constructs a new CaptureUtilityAddForm.
   *
   * @param \Drupal\image\CaptureUtilityManager $capture_utility_manager
   *   The capture utility manager.
   */
  public function __construct(CaptureUtilityManager $capture_utility_manager) {
    $this->captureUtilityManager = $capture_utility_manager;
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
  public function buildForm(array $form, FormStateInterface $form_state, WebPageArchiveInterface $web_page_archive = NULL, $capture_utility = NULL) {
    $form = parent::buildForm($form, $form_state, $web_page_archive, $capture_utility);

    $form['#title'] = $this->t('Add %label', ['%label' => $this->captureUtility->label()]);
    $form['actions']['submit']['#value'] = $this->t('Add capture utility');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCaptureUtility($capture_utility) {
    $capture_utility = $this->captureUtilityManager->createInstance($capture_utility);
    $capture_utility->setWeight(count($this->webPageArchive->getCaptureUtilities()));
    return $capture_utility;
  }

}
