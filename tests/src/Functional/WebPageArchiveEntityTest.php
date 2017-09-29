<?php

namespace Drupal\Tests\web_page_archive\Functional;

use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests web page archive.
 *
 * @group web_page_archive
 */
class WebPageArchiveEntityTest extends BrowserTestBase {

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
   * Authorized View User.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authorizedReadOnlyUser;

  /**
   * Unauthorized User.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $unauthorizedUser;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
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
    ]);
    $this->authorizedReadOnlyUser = $this->drupalCreateUser([
      'view web page archive results',
    ]);
    $this->unauthorizedUser = $this->drupalCreateUser([
      'administer nodes',
    ]);
  }

  /**
   * Functional test of adding web page archive entities via the UI.
   */
  public function testAdminEntityCreation() {
    $assert = $this->assertSession();

    // Login.
    $this->drupalLogin($this->authorizedAdminUser);

    // Verify list exists with add button.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/add');
    // Add an entity using the entity form.
    $this->drupalGet('admin/config/system/web-page-archive/add');
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'Test Archive',
        'id' => 'test_archive',
        'timeout' => 500,
        'use_cron' => 1,
        'cron_schedule' => '0 9 1 1 *',
        'url_type' => 'sitemap',
        'urls' => 'http://localhost/sitemap.xml',
      ],
      t('Create new archive')
    );
    $assert->pageTextContains('Created the Test Archive Web page archive entity.');

    // Verify previous values are retained.
    $this->assertContains('admin/config/system/web-page-archive/test_archive/edit', $this->getSession()->getCurrentUrl());
    $this->assertFieldByName('timeout', '500');
    $this->assertFieldByName('url_type', 'sitemap');
    $this->assertFieldByName('urls', 'http://localhost/sitemap.xml');

    // Add a screenshot capture utility.
    $this->drupalPostForm(
      NULL,
      [
        'new' => 'wpa_screenshot_capture',
      ],
      t('Add')
    );

    // Check field default values.
    $this->assertFieldByName('data[width]', '1280');
    $this->assertFieldByName('data[clip_width]', '1280');
    $this->assertFieldByName('data[image_type]', 'png');
    $this->assertFieldByName('data[background_color]', '#ffffff');
    $this->assertFieldByName('data[user_agent]', 'WPA');

    // Alter a few values and then submit.
    $this->drupalPostForm(
      NULL,
      [
        'data[width]' => '1400',
        'data[image_type]' => 'jpg',
      ],
      t('Add capture utility')
    );

    // Open capture utility settings.
    $this->clickLink('Edit');

    // Confirm field values.
    $this->assertFieldByName('data[width]', '1400');
    $this->assertFieldByName('data[clip_width]', '1280');
    $this->assertFieldByName('data[image_type]', 'jpg');
    $this->assertFieldByName('data[background_color]', '#ffffff');
    $this->assertFieldByName('data[user_agent]', 'WPA');

    // Attempt to change user agent and image type.
    $this->drupalPostForm(
      NULL,
      [
        'data[image_type]' => 'png',
        'data[user_agent]' => 'Testbot 5000',
      ],
      t('Update capture utility')
    );

    // Open capture utility settings.
    $this->clickLink('Edit');

    // Confirm field values.
    $this->assertFieldByName('data[width]', '1400');
    $this->assertFieldByName('data[clip_width]', '1280');
    $this->assertFieldByName('data[image_type]', 'png');
    $this->assertFieldByName('data[background_color]', '#ffffff');
    $this->assertFieldByName('data[user_agent]', 'Testbot 5000');

    // Confirm entity list shows next scheduled time.
    $this->drupalGet('admin/config/system/web-page-archive');
    $assert->pageTextContains(t('-01-01 @ 9:00am'));
    $assert->pageTextNotContains(t('Never'));

    // Update the new entity using the entity form.
    $this->drupalPostForm(
      'admin/config/system/web-page-archive/test_archive/edit',
      [
        'label' => 'Test Archiver',
        'timeout' => 250,
        'use_cron' => 0,
        'url_type' => 'url',
        'urls' => implode(PHP_EOL, [
          'http://localhost:1234/some-page',
          'http://localhost:1234/some-other-page',
        ]),
      ],
      t('Update archive')
    );
    $assert->pageTextContains('Saved the Test Archiver Web page archive entity.');

    // Verify previous values are retained.
    $this->assertFieldByName('timeout', '250');
    $this->assertFieldByName('url_type', 'url');
    $this->assertFieldByName('urls', implode(PHP_EOL, [
      'http://localhost:1234/some-page',
      'http://localhost:1234/some-other-page',
    ]));

    // Verify entity view, edit, and delete buttons are present in collection.
    // This is to ensure the entity config is correct for user operations.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/test_archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/test_archive/edit');
    $this->assertLinkByHref('admin/config/system/web-page-archive/test_archive/delete');
    $assert->pageTextContains(t('Never'));
    $assert->pageTextNotContains(t('-01-01 @ 9:00am'));
  }

  /**
   * Tests programmatic creation of web page archive entities.
   */
  public function testProgrammaticEntityCreation() {
    $assert = $this->assertSession();

    // Create a dummy entity.
    $data = [
      'label' => 'Programmatic Archive',
      'id' => 'programmatic_archive',
      'timeout' => 500,
      'use_cron' => 0,
      'url_type' => 'sitemap',
      'urls' => 'http://localhost/sitemap.xml',
      'capture_utilities' => [
        '12345678-9999-0000-5555-000000000000' => [
          'uuid' => '12345678-9999-0000-5555-000000000000',
          'id' => 'wpa_screenshot_capture',
          'weight' => 1,
          'data' => [
            'width' => 1280,
            'clip_width' => 1280,
            'background_color' => '#cc0000',
            'user_agent' => 'testbot',
            'image_type' => 'png',
          ],
        ],
        '87654321-9999-0000-5555-999999999999' => [
          'uuid' => '87654321-9999-0000-5555-999999999999',
          'id' => 'wpa_html_capture',
          'weight' => 1,
          'data' => [
            'capture' => TRUE,
          ],
        ],
      ],
    ];
    $wpa = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Login.
    $this->drupalLogin($this->authorizedAdminUser);
    $this->drupalGet('admin/config/system/web-page-archive/programmatic_archive/edit');
    $this->assertResponse(Response::HTTP_OK);
    $this->assertFieldByName('label', 'Programmatic Archive');
    $this->assertFieldByName('timeout', '500');
    $this->assertFieldByName('url_type', 'sitemap');
    $this->assertFieldByName('urls', 'http://localhost/sitemap.xml');
    $assert->pageTextContains('HTML capture utility');
    $assert->pageTextContains('Screenshot capture utility');

    // Verify run entity was created.
    $this->drupalGet('admin/config/system/web-page-archive');
    $assert->pageTextContains('Programmatic Archive');
    $this->drupalGet('admin/config/system/web-page-archive/programmatic_archive');
    $assert->pageTextContains('Programmatic Archive');
  }

  /**
   * Functional test to ensure authorized read-only access.
   */
  public function testReadOnlyUser() {
    $assert = $this->assertSession();

    // Create a dummy entity.
    $data = [
      'label' => 'Read Only Archive',
      'id' => 'read_only_archive',
      'use_cron' => 0,
      'timeout' => 500,
      'url_type' => 'sitemap',
      'urls' => 'http://localhost/sitemap.xml',
    ];
    $wpa = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Login.
    $this->drupalLogin($this->authorizedReadOnlyUser);

    $permitted_urls = [
      'admin/config/system/web-page-archive',
      'admin/config/system/web-page-archive/read_only_archive',
    ];
    foreach ($permitted_urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_OK);
    }

    $forbidden_urls = [
      'admin/config/system/web-page-archive/add',
      'admin/config/system/web-page-archive/read_only_archive/edit',
      'admin/config/system/web-page-archive/read_only_archive/delete',
      'admin/config/system/web-page-archive/read_only_archive/queue',
    ];
    foreach ($forbidden_urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_FORBIDDEN);
    }
  }

  /**
   * Functional test to ensure authorized access only.
   */
  public function testUnauthorizedUser() {
    $assert = $this->assertSession();

    // Create a dummy entity.
    $data = [
      'label' => 'Test Archive',
      'id' => 'test_archive',
      'use_cron' => 0,
      'timeout' => 500,
      'url_type' => 'sitemap',
      'urls' => 'http://localhost/sitemap.xml',
    ];
    $wpa = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Login.
    $this->drupalLogin($this->unauthorizedUser);

    $urls = [
      'admin/config/system/web-page-archive',
      'admin/config/system/web-page-archive/add',
      'admin/config/system/web-page-archive/test_archive',
      'admin/config/system/web-page-archive/test_archive/edit',
      'admin/config/system/web-page-archive/test_archive/delete',
      'admin/config/system/web-page-archive/test_archive/queue',
    ];
    foreach ($urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_FORBIDDEN);
    }
  }

  /**
   * Functional test to confirm queuing works as expected.
   */
  public function testQueuing() {
    // Create a dummy entity.
    $data = [
      'label' => 'Process and Run Archive',
      'id' => 'process_and_run_archive',
      'timeout' => 500,
      'use_cron' => 0,
      'url_type' => 'sitemap',
      'urls' => 'http://localhost/sitemap.xml',
      'capture_utilities' => [
        '12345678-9999-0000-5555-000000000000' => [
          'uuid' => '12345678-9999-0000-5555-000000000000',
          'id' => 'wpa_screenshot_capture',
          'weight' => 1,
          'data' => [
            'width' => 1280,
            'clip_width' => 1280,
            'background_color' => '#cc0000',
            'user_agent' => 'testbot',
            'image_type' => 'png',
          ],
        ],
        '87654321-9999-0000-5555-999999999999' => [
          'uuid' => '87654321-9999-0000-5555-999999999999',
          'id' => 'wpa_html_capture',
          'weight' => 1,
          'data' => [
            'capture' => TRUE,
          ],
        ],
      ],
    ];
    $wpa = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Setup mock handler.
    $mock = new MockHandler([
      new GuzzleResponse(Response::HTTP_OK, [], file_get_contents(__DIR__ . '/fixtures/sitemap.xml')),
    ]);
    $handler = HandlerStack::create($mock);

    // Start a new run.
    $entity = \Drupal::entityTypeManager()->getStorage('web_page_archive')->load('process_and_run_archive');

    // Retrieve queue.
    $queue = $entity->getQueue();

    // Ensure queue is empty.
    $this->assertEquals(0, $queue->numberOfItems());

    // Queue up items.
    $entity->startNewRun($handler);

    // Check queue.
    $this->assertEquals(4, $queue->numberOfItems());
  }

  /**
   * Tests cron processes captures.
   */
  public function testCronProcessesCaptures() {
    $assert = $this->assertSession();

    // Grab the URL of the front page.
    $capture_url = $this->getUrl();

    // Login.
    $this->drupalLogin($this->authorizedAdminUser);

    // Verify list exists with add button.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/add');

    // Add an entity using the entity form.
    $this->drupalGet('admin/config/system/web-page-archive/add');
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'localhost',
        'id' => 'localhost',
        'timeout' => 500,
        'use_cron' => 1,
        'cron_schedule' => '* * * * *',
        'url_type' => 'url',
        'urls' => $capture_url,
      ],
      t('Create new archive')
    );
    $assert->pageTextContains('Created the localhost Web page archive entity.');

    // Add the HTML capture utility.
    $this->drupalPostForm(NULL, ['new' => 'wpa_html_capture'], t('Add'));
    $assert->pageTextContains('Saved the localhost Web page archive entity.');
    $this->drupalPostForm(NULL, ['data[capture]' => '1'], t('Add capture utility'));
    $assert->pageTextContains('The capture utility was successfully applied.');

    // Allow immediate cron run.
    \Drupal::state()->set('web_page_archive.next_run.localhost', 100);

    // Simulate a cron run.
    web_page_archive_cron();

    // Check canonical view to see if run occurred.
    $this->drupalGet('admin/config/system/web-page-archive/localhost');
    $assert->pageTextContains('HTML capture utility');

    // Switched to detailed view.
    $this->clickLink('View Details');

    // Parse file path.
    if (preg_match('/(\/.*\.html)/', $this->getRawContent(), $matches)) {
      $file_path = $matches[1];
    }
    else {
      $file_path = 'this-test-will-fail.html';
    }

    // Assert file exists.
    $this->assertTrue(file_exists($file_path));

    // Delete the config entity.
    $this->drupalGet('admin/config/system/web-page-archive/localhost/delete');
    $this->drupalPostForm(NULL, [], t('Delete'));
    $assert->pageTextContains(t('content web_page_archive: deleted localhost'));
    $assert->pageTextContains(t('There is no Web Page Archive yet.'));

    // Simulate a cron run.
    web_page_archive_cron();

    // Assert file no longer exists.
    $this->assertFalse(file_exists($file_path));

  }

}
