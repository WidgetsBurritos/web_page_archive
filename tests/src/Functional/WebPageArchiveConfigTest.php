<?php

namespace Drupal\Tests\web_page_archive\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests web page archive configuration functionality.
 *
 * @group web_page_archive
 */
class WebPageArchiveConfigTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public $profile = 'minimal';

  /**
   * Authorized Admin User.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authorizedAdminUser;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'web_page_archive',
    'wpa_html_capture',
    'wpa_screenshot_capture',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->authorizedAdminUser = $this->drupalCreateUser([
      'administer web page archive',
      'view web page archive results',
      'administer site configuration',
      'import configuration',
    ]);
  }

  /**
   * Functional test of importing web page archive entities with preset uuid.
   */
  public function testEntityImportWithUuid() {
    // Import WPA entity.
    $assert = $this->assertSession();
    $this->drupalLogin($this->authorizedAdminUser);
    $this->drupalPostForm(
      'admin/config/development/configuration/single/import',
      [
        'config_type' => 'web_page_archive',
        'import' => file_get_contents(__DIR__ . '/fixtures/config-import-with-uuid.yml'),
      ],
      t('Import')
    );
    $this->drupalPostForm(NULL, [], t('Confirm'));
    $this->drupalGet('admin/config/system/web-page-archive');
    $assert->pageTextContains('Sample UUID Import Test');
    $assert->pageTextContains('sample_uuid_import_test');
    $this->clickLink('Edit');
    $this->assertFieldByName('label', 'Sample UUID Import Test');
    $this->assertFieldByName('cron_schedule', '30 * * * *');
    $this->assertFieldByName('timeout', 1250);
    $this->assertFieldByName('user_agent', 'NinjaBot');
    $this->assertFieldByName('use_cron', 1);
    $this->assertFieldByName('use_robots', 0);

    // Check HTML capture utility.
    $this->drupalGet('admin/config/system/web-page-archive/sample_uuid_import_test/utilities/7299908f-2cd3-4ff7-a902-69abab04478f');
    $this->assertFieldByName('data[capture]', 1);

    // Check Screenshot capture utility.
    $this->drupalGet('admin/config/system/web-page-archive/sample_uuid_import_test/utilities/6ff62161-18dd-4450-8b08-b906b7392ff6');
    $this->assertFieldByName('data[browser]', 'phantomjs');
    $this->assertFieldByName('data[width]', 1500);
    $this->assertFieldByName('data[background_color]', '#abc123');
    $this->assertFieldByName('data[image_type]', 'jpg');
    $this->assertFieldByName('data[delay]', 1000);
  }

  /**
   * Functional test of importing web page archive entities with null uuid.
   */
  public function testEntityImportWithNullUuid() {
    // Import WPA entity.
    $assert = $this->assertSession();
    $this->drupalLogin($this->authorizedAdminUser);
    $this->drupalPostForm(
      'admin/config/development/configuration/single/import',
      [
        'config_type' => 'web_page_archive',
        'import' => file_get_contents(__DIR__ . '/fixtures/config-import-with-null-uuid.yml'),
      ],
      t('Import')
    );
    $this->drupalPostForm(NULL, [], t('Confirm'));
    $this->drupalGet('admin/config/system/web-page-archive');
    $assert->pageTextContains('Sample Null UUID Import Test');
    $assert->pageTextContains('sample_null_uuid_import_test');
    $this->clickLink('Edit');
    $this->assertFieldByName('label', 'Sample Null UUID Import Test');
    $this->assertFieldByName('cron_schedule', '30 * * * *');
    $this->assertFieldByName('timeout', 1250);
    $this->assertFieldByName('user_agent', 'NinjaBot');
    $this->assertFieldByName('use_cron', 1);
    $this->assertFieldByName('use_robots', 0);

    // Check HTML capture utility.
    $this->drupalGet('admin/config/system/web-page-archive/sample_null_uuid_import_test/utilities/1299908f-2cd3-4ff7-a902-69abab04478f');
    $this->assertFieldByName('data[capture]', 1);

    // Check Screenshot capture utility.
    $this->drupalGet('admin/config/system/web-page-archive/sample_null_uuid_import_test/utilities/1ff62161-18dd-4450-8b08-b906b7392ff6');
    $this->assertFieldByName('data[browser]', 'chrome');
    $this->assertFieldByName('data[width]', 1500);
    $this->assertFieldByName('data[image_type]', 'jpg');
    $this->assertFieldByName('data[delay]', 1000);
    $this->assertFieldByName('data[css]', 'body { font-size: 30px; }');
    $this->assertFieldChecked('data[greyscale]');
    $this->assertFieldByName('data[click]', 'body');
  }

}
