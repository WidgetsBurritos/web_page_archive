<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\web_page_archive\Controller\WebPageArchiveController;
use Drupal\web_page_archive\Plugin\CaptureUtilityManager;
use Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityInterface;
use Drupal\web_page_archive\Parser\SitemapParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveAddForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveEditForm extends WebPageArchiveFormBase {


  /**
   * The capture utility manager service.
   *
   * @var \Drupal\web_page_archive\Plugin\CaptureUtilityManager
   */
  protected $captureUtilityManager;

  /**
   * Constructs an WebPageArchiveEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $web_page_archive_storage
   *   The storage.
   * @param \Drupal\web_page_archive\Plugin\CaptureUtilityManager $capture_utility_manager
   *   The capture utility manager service.
   * @param \Drupal\web_page_archive\Parser\SitemapParser $sitemap_parser
   *   The sitemap parser service.
   */
  public function __construct(EntityStorageInterface $web_page_archive_storage, CaptureUtilityManager $capture_utility_manager, SitemapParser $sitemap_parser = NULL) {
    parent::__construct($web_page_archive_storage, $sitemap_parser);
    $this->captureUtilityManager = $capture_utility_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('web_page_archive'),
      $container->get('plugin.manager.capture_utility'),
      $container->get('web_page_archive.parser.xml.sitemap')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();
    $form['#title'] = $this->t('Edit archive %name', ['%name' => $this->entity->label()]);
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'web_page_archive/admin';

    // Build the list of existing capture utilities for this web page archive.
    $form['capture_utilities'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Capture Utility'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'capture-utility-order-weight',
        ],
      ],
      '#attributes' => [
        'id' => 'web-page-archive-capture-utilities',
      ],
      '#empty' => $this->t('There are currently no capture utilities for this archive. Add one by selecting an option below.'),
      // Render capture utilities below parent elements.
      '#weight' => 5,
    ];
    foreach ($this->entity->getCaptureUtilities() as $capture_utility) {
      $key = $capture_utility->getUuid();
      $form['capture_utilities'][$key]['#attributes']['class'][] = 'draggable';
      $form['capture_utilities'][$key]['#weight'] = isset($user_input['capture_utilities']) ? $user_input['capture_utilities'][$key]['weight'] : NULL;
      $form['capture_utilities'][$key]['capture_utility'] = [
        '#tree' => FALSE,
        'data' => [
          'label' => [
            '#plain_text' => $capture_utility->label(),
          ],
        ],
      ];

      $summary = $capture_utility->getSummary();

      if (!empty($summary)) {
        $summary['#prefix'] = ' ';
        $form['capture_utilities'][$key]['capture_utility']['data']['summary'] = $summary;
      }

      $form['capture_utilities'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $capture_utility->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $capture_utility->getWeight(),
        '#attributes' => [
          'class' => ['capture-utility-order-weight'],
        ],
      ];

      $links = [];
      $is_configurable = $capture_utility instanceof ConfigurableCaptureUtilityInterface;
      if ($is_configurable) {
        $links['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('web_page_archive.capture_utility_edit_form', [
            'web_page_archive' => $this->entity->id(),
            'capture_utility' => $key,
          ]),
        ];
      }
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('web_page_archive.capture_utility_delete_form', [
          'web_page_archive' => $this->entity->id(),
          'capture_utility' => $key,
        ]),
      ];
      $form['capture_utilities'][$key]['operations'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }

    // Build the new capture utility addition form.
    $new_capture_utility_options = [];
    $capture_utilities = $this->captureUtilityManager->getDefinitions();
    uasort($capture_utilities, function ($a, $b) {
      return Unicode::strcasecmp($a['id'], $b['id']);
    });
    foreach ($capture_utilities as $capture_utility => $definition) {
      $new_capture_utility_options[$capture_utility] = $definition['label'];
    }
    $form['capture_utilities']['new'] = [
      '#tree' => FALSE,
      '#weight' => isset($user_input['weight']) ? $user_input['weight'] : NULL,
      '#attributes' => ['class' => ['draggable']],
    ];
    $form['capture_utilities']['new']['capture_utility'] = [
      'data' => [
        'new' => [
          '#type' => 'select',
          '#title' => $this->t('Capture Utility'),
          '#title_display' => 'invisible',
          '#options' => $new_capture_utility_options,
          '#empty_option' => $this->t('Select a new capture utility'),
        ],
        [
          'add' => [
            '#type' => 'submit',
            '#value' => $this->t('Add'),
            '#validate' => ['::captureUtilityValidate'],
            '#submit' => ['::submitForm', '::captureUtilitySave'],
          ],
        ],
      ],
      '#prefix' => '<div class="web-page-archive-capture-utility-new">',
      '#suffix' => '</div>',
    ];

    $form['capture_utilities']['new']['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight for new capture utility'),
      '#title_display' => 'invisible',
      '#default_value' => count($this->entity->getCaptureUtilities()) + 1,
      '#attributes' => ['class' => ['capture-utility-order-weight']],
    ];
    $form['capture_utilities']['new']['operations'] = [
      'data' => [],
    ];

    return parent::form($form, $form_state);
  }

  /**
   * Validate handler for capture utility.
   */
  public function captureUtilityValidate($form, FormStateInterface $form_state) {
    if (!$form_state->getValue('new')) {
      $form_state->setErrorByName('new', $this->t('Select a capture utility to add.'));
    }
  }

  /**
   * Submit handler for capture utility.
   */
  public function captureUtilitySave($form, FormStateInterface $form_state) {
    $this->save($form, $form_state);

    // Check if this field has any configuration options.
    $capture_utility = $this->captureUtilityManager->getDefinition($form_state->getValue('new'));

    // Load the configuration form for this option.
    if (is_subclass_of($capture_utility['class'], 'Drupal\web_page_archive\Plugin\ConfigurableCaptureUtilityInterface')) {
      $form_state->setRedirect(
        'web_page_archive.capture_utility_add_form',
        [
          'web_page_archive' => $this->entity->id(),
          'capture_utility' => $form_state->getValue('new'),
        ],
        ['query' => ['weight' => $form_state->getValue('weight')]]
      );
    }
    // If there's no form, immediately add the capture utility.
    else {
      $capture_utility = [
        'id' => $capture_utility['id'],
        'data' => [],
        'weight' => $form_state->getValue('weight'),
      ];
      $capture_utility_id = $this->entity->addCaptureUtility($capture_utility);
      $this->entity->save();
      if (!empty($capture_utility_id)) {
        $this->messenger()->addStatus($this->t('The capture utility was successfully applied.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Update capture utility weights.
    if (!$form_state->isValueEmpty('capture_utilities')) {
      $this->updateCaptureUtilityWeights($form_state->getValue('capture_utilities'));
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $this->messenger()->addStatus($this->t('Saved the %label Web page archive entity.', [
      '%label' => $this->entity->label(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    // If we're missing dependencies, we shouldn't have a save button.
    if (!WebPageArchiveController::checkDependencies()) {
      return [];
    }

    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update archive');

    return $actions;
  }

  /**
   * Updates capture utility weights.
   *
   * @param array $capture_utilities
   *   Associative array with capture utilities having capture utility uuid
   *   as keys and array with capture utility data as values.
   */
  protected function updateCaptureUtilityWeights(array $capture_utilities) {
    foreach ($capture_utilities as $uuid => $capture_utility_data) {
      if ($this->entity->getCaptureUtilities()->has($uuid)) {
        $this->entity->getCaptureUtility($uuid)->setWeight($capture_utility_data['weight']);
      }
    }
  }

}
