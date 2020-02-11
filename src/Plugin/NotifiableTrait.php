<?php

namespace Drupal\web_page_archive\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Trait for utility plugins that allow notifications.
 */
trait NotifiableTrait {

  /**
   * Notification utility manager service.
   *
   * @var \Drupal\web_page_archive\Plugin\NotificationUtilityManager
   */
  protected $notificationUtilityManager;

  /**
   * Sets the notification utility manager service.
   *
   * @param \Drupal\web_page_archive\Plugin\NotificationUtilityManager $notification_utility_manager
   *   The notification utility manager service.
   *
   * @return self
   *   The current instance.
   */
  public function setNotificationUtilityManager(NotificationUtilityManager $notification_utility_manager) {
    $this->notificationUtilityManager = $notification_utility_manager;
    return $this;
  }

  /**
   * Retrieves the notification utility manager service.
   *
   * @return \Drupal\web_page_archive\Plugin\NotificationUtilityManager
   *   Notification utility manager service.
   */
  public function getNotificationUtilityManager() {
    if (!isset($this->notificationUtilityManager)) {
      $this->setNotificationUtilityManager(\Drupal::service('plugin.manager.notification_utility'));
    }

    return $this->notificationUtilityManager;
  }

  /**
   * Injects the default notification values if applicable.
   *
   * @param array &$values
   *   Reference to the array to change.
   * @param array $defaults
   *   List of default config settings.
   */
  public function injectNotificationDefaultValues(array &$values, array $defaults) {
    if (isset($defaults['wpa_notification_utility']) && !isset($values['wpa_notification_utility'])) {
      $values['wpa_notification_utility'] = $defaults['wpa_notification_utility'];
    }
    if (isset($defaults['wpa_notification_utility_details']) && !isset($values['wpa_notification_utility_details'])) {
      $values['wpa_notification_utility_details'] = $defaults['wpa_notification_utility_details'];
    }
  }

  /**
   * Injects notification field values into configuration array on form submit.
   *
   * @param array &$configuration
   *   Configuration array to modify.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function injectNotificationConfig(array &$configuration, FormStateInterface $form_state) {
    $configuration['wpa_notification_utility'] = $form_state->getValue('wpa_notification_utility');
    $configuration['wpa_notification_utility_details'] = $form_state->getValue('wpa_notification_utility_details');
  }

  /**
   * Retrieves an HTML list describing which variables are available.
   *
   * @param string $context
   *   Context which we want the variable list for.
   *
   * @return string
   *   HTML string containing variable list.
   */
  protected function getVariableListHtml($context) {
    $variables = $this->getReplacementListByContext($context);
    if (empty($variables)) {
      return '';
    }
    $ret = $this->t('The following variables are available to the above field(s):');
    $ret .= '<blockquote><dl>';
    foreach ($variables as $variable => $label) {
      $ret .= "<dt><code>{$variable}</code></dt> <dd>{$label}</dd>";
    }
    $ret .= '</dl></blockquote>';
    return $ret;
  }

  /**
   * Injects notification fields into the specified form array.
   *
   * @param array &$form
   *   Form array to modify.
   * @param array $configuration
   *   Current configuration settings.
   */
  public function injectNotificationFields(array &$form, array $configuration) {
    $definitions = $this->getNotificationUtilityManager()->getDefinitions();

    // Present list of all notification utilities.
    $options = [];
    foreach ($definitions as $id => $details) {
      $options[$id] = $details['label'];
    }

    $form['wpa_notification_utility'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Notification Method(s)'),
      '#options' => $options,
      '#default_value' => isset($configuration['wpa_notification_utility']) ? $configuration['wpa_notification_utility'] : [],
    ];

    foreach ($definitions as $id => $details) {
      foreach ($this->getNotificationContexts() as $context_id => $context) {
        // Create fieldset for notification method.
        $replacements = [
          '@details_label' => $details['label'],
          '@context_label' => $context['label'],
        ];
        $form['wpa_notification_utility_details'][$id][$context_id] = [
          '#type' => 'details',
          '#tree' => TRUE,
          '#title' => $this->t('@details_label - @context_label', $replacements),
          '#description' => $context['description'],
          '#states' => [
            'visible' => [
              ":input[name='data[wpa_notification_utility][{$id}]']" => ['checked' => TRUE],
            ],
          ],
          'enabled' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable this notification utility'),
            '#default_value' => !empty($configuration['wpa_notification_utility_details'][$id][$context_id]['enabled']) ? $configuration['wpa_notification_utility_details'][$id][$context_id]['enabled'] : FALSE,
          ],
        ];

        // Add form fields from notification utility.
        $form['wpa_notification_utility_details'][$id][$context_id] += $details['class']::getFormFields($this->getVariableListHtml($context_id));

        // Populate default values from capture utility settings.
        foreach (Element::children($form['wpa_notification_utility_details'][$id][$context_id]) as $field_id) {
          if ($field_id != 'enabled' && isset($configuration['wpa_notification_utility_details'][$id][$context_id][$field_id])) {
            $form['wpa_notification_utility_details'][$id][$context_id][$field_id]['#default_value'] = $configuration['wpa_notification_utility_details'][$id][$context_id][$field_id];
            $form['wpa_notification_utility_details'][$id][$context_id][$field_id]['#states'] = [
              'visible' => [
                ":input[name='data[wpa_notification_utility_details][{$id}][{$context_id}][enabled]']" => ['checked' => TRUE],
              ],
            ];
            if (!empty($form['wpa_notification_utility_details'][$id][$context_id][$field_id]['#required'])) {
              $form['wpa_notification_utility_details'][$id][$context_id][$field_id]['#states']['required'] = $form['wpa_notification_utility_details'][$id][$context_id][$field_id]['#states']['visible'];
              unset($form['wpa_notification_utility_details'][$id][$context_id][$field_id]['#required']);
            }
          }
        }
      }
    }
  }

  /**
   * Attempts to notify based on the specified context and replacements.
   *
   * @param string $context
   *   The context string.
   * @param array $replacements
   *   List of variables to replace - similar to t() functionality.
   */
  public function notify($context, array $replacements = []) {
    if (!empty($this->configuration['wpa_notification_utility'])) {
      foreach ($this->configuration['wpa_notification_utility'] as $notification_utility) {
        if (!empty($notification_utility)) {
          if (!empty($this->configuration['wpa_notification_utility_details'][$notification_utility][$context]['enabled'])) {
            $notify = $this->getNotificationUtilityManager()->createInstance($notification_utility);
            if (isset($notify)) {
              $notify->triggerEvent($this->configuration['wpa_notification_utility_details'][$notification_utility][$context], $replacements);
            }
          }
        }
      }
    }
  }

  /**
   * Retrieves list of notification context for notifiable utilities.
   *
   * @return array
   *   List of notification contexts.
   */
  abstract public function getNotificationContexts();

  /**
   * Retrieves variable replacement list based on complex.
   *
   * @param string $context
   *   The context.
   *
   * @return array
   *   The replacement list.
   */
  abstract public function getReplacementListByContext($context);

}
