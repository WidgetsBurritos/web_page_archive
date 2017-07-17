<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\web_page_archive\Plugin\CaptureUtilityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebPageArchiveAddForm.
 *
 * @package Drupal\web_page_archive\Form
 */
class WebPageArchiveEditForm extends WebPageArchiveFormBase {


    /**
     * The image effect manager service.
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
     */
    public function __construct(EntityStorageInterface $web_page_archive_storage, CaptureUtilityManager $capture_utility_manager) {
      parent::__construct($web_page_archive_storage);
      $this->captureUtilityManager = $capture_utility_manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('entity.manager')->getStorage('web_page_archive'),
        $container->get('plugin.manager.capture_utility')
      );
    }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);


    // Build the list of existing capture utilities for this web page archive.
    $form['capture_utilities'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Capture Utility'),
        // $this->t('Weight'),
        $this->t('Operations'),
      ],
      // '#tabledrag' => [
      //   [
      //     'action' => 'order',
      //     'relationship' => 'sibling',
      //     'group' => 'capture-utility-order-weight',
      //   ],
      // ],
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
      // $form['capture_utilities'][$key]['#weight'] = isset($user_input['capture_utilities']) ? $user_input['capture_utilities'][$key]['weight'] : NULL;
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

      // $form['capture_utilities'][$key]['weight'] = [
      //   '#type' => 'weight',
      //   '#title' => $this->t('Weight for @title', ['@title' => $capture_utility->label()]),
      //   '#title_display' => 'invisible',
      //   '#default_value' => $capture_utility->getWeight(),
      //   '#attributes' => [
      //     'class' => ['capture-utility-order-weight'],
      //   ],
      // ];

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
      // TODO: Fix this:
      // $links['delete'] = [
      //   'title' => $this->t('Delete'),
      //   'url' => Url::fromRoute('web_page_archive.capture_utility_delete_form', [
      //     'web_page_archive' => $this->entity->id(),
      //     'capture_utility' => $key,
      //   ]),
      // ];
      $form['capture_utilities'][$key]['operations'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }

    // Build the new image effect addition form and add it to the effect list.
    $new_capture_utility_options = [];
    $capture_utilities = $this->captureUtilityManager->getDefinitions();
    uasort($capture_utilities, function ($a, $b) {
      return Unicode::strcasecmp($a['label'], $b['label']);
    });
    foreach ($capture_utilities as $capture_utility => $definition) {
      $new_capture_utility_options[$capture_utility] = $definition['label'];
    }
    $form['capture_utilities']['new'] = [
      '#tree' => FALSE,
      // '#weight' => isset($user_input['weight']) ? $user_input['weight'] : NULL,
      // '#attributes' => ['class' => ['draggable']],
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
            '#validate' => ['::captueUtilityValidate'],
            '#submit' => ['::submitForm', '::captureUtilitySave'],
          ],
        ],
      ],
      '#prefix' => '<div class="image-style-new">',
      '#suffix' => '</div>',
    ];
    //
    // $form['capture_utilities']['new']['weight'] = [
    //   '#type' => 'weight',
    //   '#title' => $this->t('Weight for new effect'),
    //   '#title_display' => 'invisible',
    //   '#default_value' => count($this->entity->getEffects()) + 1,
    //   '#attributes' => ['class' => ['capture-utility-order-weight']],
    // ];
    // $form['capture_utilities']['new']['operations'] = [
    //   'data' => [],
    // ];

    return $form;
  }



    /**
     * Submit handler for image effect.
     */
    public function effectSave($form, FormStateInterface $form_state) {
      $this->save($form, $form_state);

      // Check if this field has any configuration options.
      $effect = $this->imageEffectManager->getDefinition($form_state->getValue('new'));

      // Load the configuration form for this option.
      if (is_subclass_of($effect['class'], '\Drupal\image\ConfigurableImageEffectInterface')) {
        $form_state->setRedirect(
          'image.effect_add_form',
          [
            'image_style' => $this->entity->id(),
            'image_effect' => $form_state->getValue('new'),
          ],
          ['query' => ['weight' => $form_state->getValue('weight')]]
        );
      }
      // If there's no form, immediately add the image effect.
      else {
        $effect = [
          'id' => $effect['id'],
          'data' => [],
          'weight' => $form_state->getValue('weight'),
        ];
        $effect_id = $this->entity->addImageEffect($effect);
        $this->entity->save();
        if (!empty($effect_id)) {
          drupal_set_message($this->t('The image effect was successfully applied.'));
        }
      }
    }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Update image effect weights.
    if (!$form_state->isValueEmpty('effects')) {
      $this->updateEffectWeights($form_state->getValue('effects'));
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
  }

}
