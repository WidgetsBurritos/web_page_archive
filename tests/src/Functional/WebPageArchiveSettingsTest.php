<?php

namespace Drupal\Tests\web_page_archive\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests web page archive configuration functionality.
 *
 * @group web_page_archive
 */
class WebPageArchiveSettingsTest extends BrowserTestBase {

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
    'wpa_skeleton_capture',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->authorizedAdminUser = $this->drupalCreateUser([
      'administer web page archive',
      'view web page archive results',
    ]);
  }

  /**
   * Functional test of default WPA settings.
   */
  public function testSettingsExistAndHaveDefaultValues() {
    $assert = $this->assertSession();
    $this->drupalLogin($this->authorizedAdminUser);

    // Ensure settings link is exposed in UI.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->clickLink('Settings');

    // Confirm default entity settings exist and populate defaults.
    $assert->pageTextContains('Default Entity Settings');
    $this->assertFieldByName('system[node_path]', '');
    $this->assertFieldByName('system[npm_path]', '');
    $this->assertFieldByName('cron[capture_max]', 100);
    $this->assertFieldByName('cron[file_cleanup]', 50);
    $this->assertFieldByName('defaults[label]', '');
    $this->assertFieldByName('defaults[cron_schedule]', '@weekly');
    $this->assertFieldByName('defaults[timeout]', 500);
    $this->assertFieldByName('defaults[url_type]', 'url');
    $this->assertFieldByName('defaults[user_agent]', 'WPA');
    $this->assertFieldByName('defaults[use_cron]', 1);
    $this->assertFieldByName('defaults[use_robots]', 1);

    // Attempt to set defaults.
    $this->drupalPostForm(
      NULL,
      [
        'system[node_path]' => '/path/to/node',
        'system[npm_path]' => '/path/to/npm',
        'cron[capture_max]' => 500,
        'cron[file_cleanup]' => 150,
        'defaults[label]' => 'New default label',
        'defaults[cron_schedule]' => '30 * * * *',
        'defaults[timeout]' => 1500,
        'defaults[url_type]' => 'sitemap',
        'defaults[user_agent]' => 'NinjaBot',
        'defaults[use_robots]' => 0,
      ],
      t('Save configuration')
    );

    // Confirm default entity settings updated.
    $this->assertFieldByName('system[node_path]', '/path/to/node');
    $this->assertFieldByName('system[npm_path]', '/path/to/npm');
    $this->assertFieldByName('cron[capture_max]', 500);
    $this->assertFieldByName('cron[file_cleanup]', 150);
    $this->assertFieldByName('defaults[label]', 'New default label');
    $this->assertFieldByName('defaults[cron_schedule]', '30 * * * *');
    $this->assertFieldByName('defaults[timeout]', 1500);
    $this->assertFieldByName('defaults[url_type]', 'sitemap');
    $this->assertFieldByName('defaults[user_agent]', 'NinjaBot');
    $this->assertFieldByName('defaults[use_robots]', 0);

    // Ensure default values made it into the add form.
    $this->drupalGet('admin/config/system/web-page-archive/add');
    $this->assertFieldByName('label', 'New default label');
    $this->assertFieldByName('cron_schedule', '30 * * * *');
    $this->assertFieldByName('timeout', 1500);
    $this->assertFieldByName('url_type', 'sitemap');
    $this->assertFieldByName('user_agent', 'NinjaBot');
    $this->assertFieldByName('use_robots', 0);
  }

  /**
   * Functional test of default capture utility settings.
   */
  public function testCaptureUtilitySettingsExistAndHaveDefaultValues() {
    $assert = $this->assertSession();
    $this->drupalLogin($this->authorizedAdminUser);

    // Ensure settings link is exposed in UI.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->clickLink('Settings');

    // Confirm HTML capture utility section exists and populates defaults.
    $assert->pageTextContains('HTML capture utility Settings');
    $this->assertFieldByName('wpa_html_capture[defaults][capture]', 1);

    // Confirm screenshot capture utility section exists and populates defaults.
    $assert->pageTextContains('Screenshot capture utility Settings');
    $this->assertFieldByName('wpa_screenshot_capture[defaults][browser]', 'chrome');
    $this->assertFieldByName('wpa_screenshot_capture[defaults][delay]', 0);
    $this->assertFieldByName('wpa_screenshot_capture[defaults][image_type]', 'png');
    $this->assertFieldByName('wpa_screenshot_capture[defaults][width]', 1280);
    $this->assertFieldByName('wpa_skeleton_capture[defaults][width]', 1280);

    // Attempt to set defaults.
    $this->drupalPostForm(
      NULL,
      [
        'wpa_html_capture[defaults][capture]' => 0,
        'wpa_screenshot_capture[defaults][browser]' => 'phantomjs',
        'wpa_screenshot_capture[defaults][background_color]' => '#abc123',
        'wpa_screenshot_capture[defaults][delay]' => 150,
        'wpa_screenshot_capture[defaults][image_type]' => 'jpg',
        'wpa_screenshot_capture[defaults][width]' => 1400,
        'wpa_skeleton_capture[defaults][width]' => 480,
      ],
      t('Save configuration')
    );

    // Confirm HTML capture utility settings updated.
    $this->assertFieldByName('wpa_html_capture[defaults][capture]', 0);

    // Confirm screenshot capture utility settings updated.
    $this->assertFieldByName('wpa_screenshot_capture[defaults][browser]', 'phantomjs');
    $this->assertFieldByName('wpa_screenshot_capture[defaults][background_color]', '#abc123');
    $this->assertFieldByName('wpa_screenshot_capture[defaults][delay]', 150);
    $this->assertFieldByName('wpa_screenshot_capture[defaults][image_type]', 'jpg');
    $this->assertFieldByName('wpa_screenshot_capture[defaults][width]', 1400);

    // Confirm skeleton capture utility settings updated.
    $this->assertFieldByName('wpa_skeleton_capture[defaults][width]', 480);

    // Create a dummy entity with no capture utilities.
    $data = [
      'label' => 'Programmatic Archive',
      'id' => 'programmatic_archive',
      'timeout' => 500,
      'use_cron' => 0,
      'use_robots' => 0,
      'url_type' => 'sitemap',
      'user_agent' => 'testbot',
      'urls' => 'http://localhost/sitemap.xml',
      'capture_utilities' => [],
    ];
    $wpa = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Add a HTML capture utility to the config entity and test defaults.
    $this->drupalGet('admin/config/system/web-page-archive/programmatic_archive/edit');
    $this->drupalPostForm(NULL, ['new' => 'wpa_html_capture'], t('Add'));
    $this->assertNoFieldChecked('data[capture]');

    // Add a screenshot capture utilitiy to the config entity and test defaults.
    $this->drupalGet('admin/config/system/web-page-archive/programmatic_archive/edit');
    $this->drupalPostForm(NULL, ['new' => 'wpa_screenshot_capture'], t('Add'));
    $this->assertFieldByName('data[browser]', 'phantomjs');
    $this->assertFieldByName('data[background_color]', '#abc123');
    $this->assertFieldByName('data[delay]', 150);
    $this->assertFieldByName('data[image_type]', 'jpg');
    $this->assertFieldByName('data[width]', 1400);

    // Add a screenshot capture utilitiy to the config entity and test defaults.
    $this->drupalGet('admin/config/system/web-page-archive/programmatic_archive/edit');
    $this->drupalPostForm(NULL, ['new' => 'wpa_skeleton_capture'], t('Add'));
    $this->assertFieldByName('data[width]', 480);

  }

}
