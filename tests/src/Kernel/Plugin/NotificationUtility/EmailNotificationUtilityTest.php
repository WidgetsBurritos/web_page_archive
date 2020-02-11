<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\NotificationUtility;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Test\AssertMailTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests email notification utility.
 *
 * @group web_page_archive
 */
class EmailNotificationUtilityTest extends KernelTestBase {

  use AssertMailTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['web_page_archive', 'dblog'];

  /**
   * Email notification utility service.
   *
   * @var \Drupal\web_page_archive\Plugin\NotificationUtility\EmailNotificationUtility
   */
  protected $email;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installSchema('dblog', ['watchdog']);
    $this->email = $this->container->get('plugin.manager.notification_utility')->createInstance('wpa_notify_email');
  }

  /**
   * Tests that email is sent.
   */
  public function testEmailIsSent() {
    // Create new email values.
    $subject = $this->randomString(64);
    $body = $this->randomString(256);
    $config = [
      'to' => 'foobar@example.com',
      'subject' => $subject,
      'body' => $body,
      'format' => 'text/plain',
    ];

    // Before we send the email, \Drupal\Core\Test\AssertMailTrait::getMails()
    // should return an empty array.
    $captured_emails = $this->getMails();
    $this->assertCount(0, $captured_emails, 'The captured emails queue is empty.');

    // Trigger the event, which should send emails.
    $this->email->triggerEvent($config);

    // Ensure that there is one email in the captured emails array.
    $captured_emails = $this->getMails();
    $this->assertEquals(count($captured_emails), 1, 'One email was captured.');

    foreach ($config as $field => $value) {
      $this->assertMail($field, $value, "The email was sent and the value for property $field is intact.");
    }
  }

  /**
   * Tests invalid config gets added to watchdog table.
   */
  public function testInvalidConfigGetsLogged() {
    $config = [];
    $this->email->triggerEvent($config);

    // Query the watchdog table looking for a warning.
    $db = $this->container->get('database');
    $query = $db->select('watchdog', 'w');
    $query->fields('w', ['type', 'message', 'severity']);
    $query->condition('type', 'web_page_archive');

    $expected = [
      'type' => 'web_page_archive',
      'message' => 'Missing email setting: to',
      'severity' => RfcLogLevel::WARNING,
    ];
    $this->assertEquals($expected, $query->execute()->fetchAssoc());
  }

}
