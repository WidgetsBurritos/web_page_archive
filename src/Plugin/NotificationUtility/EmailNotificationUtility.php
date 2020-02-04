<?php

namespace Drupal\web_page_archive\Plugin\NotificationUtility;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\web_page_archive\Event\NotificationEvent;
use Drupal\web_page_archive\Plugin\NotificationUtilityBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a mechanism for WPA to notify users via email.
 *
 * @NotificationUtility(
 *   id = "wpa_notify_email",
 *   label = @Translation("Notify: Email", context = "Web Page Archive"),
 *   description = @Translation("Sends notifications to users via email.", context = "Web Page Archive"),
 * )
 */
class EmailNotificationUtility extends NotificationUtilityBase {

  /**
   * Mail manager service.
   *
   * @var Drupal\Core\Mail\MailManagerInterface
   */
  protected $mail;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, EventDispatcherInterface $event_dispatcher, MailManagerInterface $mail) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $language_manager, $config_factory, $event_dispatcher);

    $this->mail = $mail;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('web_page_archive'),
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('event_dispatcher'),
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getFormFields($variable_list) {
    return [
      'to' => [
        '#type' => 'email',
        '#title' => t('Recipient Email Address'),
        '#default_value' => \Drupal::configFactory()->get('system.site')->get('mail'),
        '#required' => TRUE,
      ],
      'format' => [
        '#type' => 'select',
        '#title' => t('Email Format'),
        '#options' => [
          'text/plain' => t('Plain text'),
          'text/html' => t('HTML'),
        ],
        '#default_value' => 'text/plain',
        '#required' => TRUE,
      ],
      'subject' => [
        '#type' => 'textfield',
        '#title' => t('Email Subject'),
        '#default_value' => t('Email subject goes here.'),
        '#required' => TRUE,
      ],
      'body' => [
        '#type' => 'textarea',
        '#title' => t('Email Body'),
        '#default_value' => t('Email body content goes here.'),
        '#description' => $variable_list,
        '#required' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredConfigurationKeys() {
    return ['to', 'body', 'subject', 'format'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfigurationValues() {
    return [
      'headers' => ['Content-type' => 'text/plain'],
      'from' => $this->config->get('system.site')->get('mail'),
      'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function handleEvent(NotificationEvent $event) {
    $config = $event->getEventConfiguration();
    $replacements = $event->getReplacements();
    try {
      $this->checkRequiredConfigurationKeys($config);
      $config = $config + $this->defaultConfigurationValues();
      $config['headers']['Content-type'] = $config['format'];
      $config['body'] = new FormattableMarkup($config['body'], $replacements);
      $config['subject'] = new FormattableMarkup($config['subject'], $replacements);
      $this->mail->getInstance(['module' => $this->getPluginDefinition()['provider'], 'key' => $this->getPluginId()])->mail($config);
    }
    catch (\Exception $e) {
      $this->logger->warning($e->getMessage());
    }
  }

}
