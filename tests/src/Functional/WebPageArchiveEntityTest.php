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
    $this->assertLinkByHref('admin/config/system/web-page-archive/jobs/add');
    // Add an entity using the entity form.
    $this->drupalGet('admin/config/system/web-page-archive/jobs/add');
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'Test Archive',
        'id' => 'test_archive',
        'timeout' => 500,
        'use_cron' => 1,
        'use_robots' => 0,
        'user_agent' => 'MonkeyBot',
        'cron_schedule' => '0 9 1 1 *',
        'url_type' => 'url',
        'urls' => 'http://localhost',
      ],
      t('Create new archive')
    );
    $assert->pageTextContains('Created the Test Archive Web page archive entity.');

    // Verify previous values are retained.
    $this->assertContains('admin/config/system/web-page-archive/jobs/test_archive/edit', $this->getSession()->getCurrentUrl());
    $this->assertFieldByName('timeout', '500');
    $this->assertFieldByName('url_type', 'url');
    $this->assertFieldByName('urls', 'http://localhost');
    $this->assertFieldByName('user_agent', 'MonkeyBot');

    // Add a screenshot capture utility.
    $this->drupalPostForm(
      NULL,
      [
        'new' => 'wpa_screenshot_capture',
      ],
      t('Add')
    );

    // Check field default values.
    $this->assertFieldByName('data[browser]', 'chrome');
    $this->assertFieldByName('data[width]', '1280');
    $this->assertFieldByName('data[image_type]', 'png');
    $this->assertFieldByName('data[delay]', '0');
    $this->assertFieldByName('data[css]', '');
    $this->assertNoFieldChecked('data[greyscale]');
    $this->assertFieldByName('data[click]', '');

    // Alter a few values and then submit.
    $this->drupalPostForm(
      NULL,
      [
        'data[width]' => '1400',
        'data[image_type]' => 'jpg',
        'data[delay]' => '250',
        'data[css]' => 'body { font-weight: 900; }',
        'data[greyscale]' => TRUE,
        'data[click]' => 'body',
      ],
      t('Add capture utility')
    );

    // Open capture utility settings.
    $this->clickLink('Edit');

    // Confirm field values.
    $this->assertFieldByName('data[browser]', 'chrome');
    $this->assertFieldByName('data[width]', '1400');
    $this->assertFieldByName('data[image_type]', 'jpg');
    $this->assertFieldByName('data[delay]', '250');
    $this->assertFieldByName('data[css]', 'body { font-weight: 900; }');
    $this->assertFieldChecked('data[greyscale]');
    $this->assertFieldByName('data[click]', 'body');

    // Attempt to image type.
    $this->drupalPostForm(
      NULL,
      [
        'data[browser]' => 'phantomjs',
        'data[background_color]' => '#ffffff',
        'data[image_type]' => 'png',
      ],
      t('Update capture utility')
    );

    // Open capture utility settings.
    $this->clickLink('Edit');

    // Confirm field values.
    $this->assertFieldByName('data[browser]', 'phantomjs');
    $this->assertFieldByName('data[width]', '1400');
    $this->assertFieldByName('data[image_type]', 'png');
    $this->assertFieldByName('data[background_color]', '#ffffff');
    $this->assertFieldByName('data[delay]', '250');

    // Confirm entity list shows next scheduled time.
    $this->drupalGet('admin/config/system/web-page-archive');
    $assert->pageTextContains(t('-01-01 @ 9:00am'));
    $assert->pageTextNotContains(t('Never'));

    // Update the new entity using the entity form.
    $this->drupalPostForm(
      'admin/config/system/web-page-archive/jobs/test_archive/edit',
      [
        'label' => 'Test Archiver',
        'timeout' => 250,
        'use_cron' => 0,
        'use_robots' => 0,
        'user_agent' => 'Secret Agent Man',
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
    $this->assertFieldByName('user_agent', 'Secret Agent Man');
    $this->assertFieldByName('urls', implode(PHP_EOL, [
      'http://localhost:1234/some-page',
      'http://localhost:1234/some-other-page',
    ]));

    // Verify entity view, edit, and delete buttons are present in collection.
    // This is to ensure the entity config is correct for user operations.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/jobs/test_archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/jobs/test_archive/edit');
    $this->assertLinkByHref('admin/config/system/web-page-archive/jobs/test_archive/delete');
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
      'use_robots' => 0,
      'url_type' => 'sitemap',
      'user_agent' => 'testbot',
      'urls' => 'http://localhost/sitemap.xml',
      'capture_utilities' => [
        '12345678-9999-0000-5555-000000000000' => [
          'uuid' => '12345678-9999-0000-5555-000000000000',
          'id' => 'wpa_screenshot_capture',
          'weight' => 1,
          'data' => [
            'browser' => 'phantomjs',
            'width' => 1280,
            'background_color' => '#cc0000',
            'image_type' => 'png',
            'delay' => 0,
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
        '00000000-9999-0000-5555-333333333333' => [
          'uuid' => '00000000-9999-0000-5555-333333333333',
          'id' => 'wpa_skeleton_capture',
          'weight' => 1,
          'data' => [
            'width' => 480,
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
    $this->drupalGet('admin/config/system/web-page-archive/jobs/programmatic_archive/edit');
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
    $this->drupalGet('admin/config/system/web-page-archive/jobs/programmatic_archive');
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
      'use_robots' => 0,
      'timeout' => 500,
      'url_type' => 'url',
      'urls' => 'http://localhost',
    ];
    $wpa = \Drupal::entityTypeManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Login.
    $this->drupalLogin($this->authorizedReadOnlyUser);

    $permitted_urls = [
      'admin/config/system/web-page-archive',
      'admin/config/system/web-page-archive/jobs/read_only_archive',
    ];
    foreach ($permitted_urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_OK);
    }

    $forbidden_urls = [
      'admin/config/system/web-page-archive/jobs/add',
      'admin/config/system/web-page-archive/jobs/read_only_archive/edit',
      'admin/config/system/web-page-archive/jobs/read_only_archive/delete',
      'admin/config/system/web-page-archive/jobs/read_only_archive/queue',
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
      'use_robots' => 0,
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
      'admin/config/system/web-page-archive/jobs/add',
      'admin/config/system/web-page-archive/jobs/test_archive',
      'admin/config/system/web-page-archive/jobs/test_archive/edit',
      'admin/config/system/web-page-archive/jobs/test_archive/delete',
      'admin/config/system/web-page-archive/jobs/test_archive/queue',
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
      'use_robots' => 0,
      'url_type' => 'sitemap',
      'urls' => 'http://localhost/sitemap.xml',
      'user_agent' => 'testbot',
      'capture_utilities' => [
        '12345678-9999-0000-5555-000000000000' => [
          'uuid' => '12345678-9999-0000-5555-000000000000',
          'id' => 'wpa_screenshot_capture',
          'weight' => 1,
          'data' => [
            'browser' => 'phantomjs',
            'width' => 1280,
            'background_color' => '#cc0000',
            'image_type' => 'png',
            'delay' => 0,
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
    $base_url = $this->getUrl();
    $urls = [
      // Front page is crawlable via default Drupal robots.txt.
      $base_url,
      // Front page is crawlable via default Drupal robots.txt.
      $base_url . '?somearg=1',
      // Login page is not crawlable via default Drupal robots.txt.
      $base_url . '/user/login/',
    ];

    // Login.
    $this->drupalLogin($this->authorizedAdminUser);

    // Verify list exists with add button.
    $this->drupalGet('admin/config/system/web-page-archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/jobs/add');

    // Add an entity using the entity form.
    $this->drupalGet('admin/config/system/web-page-archive/jobs/add');
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'localhost',
        'id' => 'localhost',
        'timeout' => 500,
        'use_cron' => 1,
        'use_robots' => 1,
        'user_agent' => 'WPA',
        'cron_schedule' => '* * * * *',
        'url_type' => 'url',
        'urls' => implode(PHP_EOL, $urls),
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
    $this->drupalGet('admin/config/system/web-page-archive/jobs/localhost');
    $assert->pageTextContains('HTML capture utility');

    // Switched to detailed view.
    $this->clickLink('View Details');

    // Parse file path.
    $file_paths = [];
    if (preg_match_all('/<span class="wpa-hidden wpa-file-path">(.*\.html)<\/span>/', $this->getSession()->getPage()->getContent(), $matches)) {
      $file_paths = $matches[1];

      // Despite attempting to capture two URLs we should only capture 1 due
      // to robots.txt restrictions.
      $this->assertEquals(2, count($matches[1]));
    }
    else {
      $file_paths[] = 'this-test-will-fail.html';
    }

    // Assert files exist.
    $this->assertTrue(file_exists($file_paths[0]));
    $this->assertTrue(file_exists($file_paths[1]));

    // Attempt to download captures.
    $this->drupalGet('admin/config/system/web-page-archive/jobs/localhost');
    $this->clickLink('Download Run');
    $assert->pageTextContains('You can download all images from the specified run as a *.zip file.');
    $this->drupalPostForm(NULL, [], t('Download Run'));

    // Ensure our response contains the expected headers.
    $assert->responseHeaderEquals('Content-Type', 'application/zip;charset=UTF-8');
    $assert->responseHeaderContains('Content-Disposition', 'attachment; filename="localhost-');

    // Ensure our response is a valid zip file containing the expected files.
    $tmp_file = \file_directory_temp() . '/tmp.zip';
    file_put_contents($tmp_file, $this->getSession()->getPage()->getContent());
    $zip = new \ZipArchive();
    $zip->open($tmp_file);
    $this->assertEquals(3, $zip->numFiles);

    // Verify the first file.
    $file_name_1 = basename($file_paths[0]);
    $this->assertEquals($file_name_1, $zip->statIndex(0)['name']);
    $this->assertEquals(file_get_contents($file_paths[0]), $zip->getFromIndex(0));

    // Verify the second file.
    $file_name_2 = basename($file_paths[1]);
    $this->assertEquals($file_name_2, $zip->statIndex(1)['name']);
    $this->assertEquals(file_get_contents($file_paths[1]), $zip->getFromIndex(1));

    // Verify the summary file.
    $this->assertEquals('summary.csv', $zip->statIndex(2)['name']);
    $expected_summary = implode([
      'Url,File',
      "{$base_url},{$file_name_1}",
      "{$base_url}?somearg=1,{$file_name_2}",
      '',
    ], PHP_EOL);
    $this->assertEquals($expected_summary, $zip->getFromIndex(2));
    $zip->close();

    // Delete the config entity.
    $this->drupalGet('admin/config/system/web-page-archive/jobs/localhost/delete');
    $this->drupalPostForm(NULL, [], t('Delete'));
    $assert->pageTextContains(t('content web_page_archive: deleted localhost'));
    $assert->pageTextContains(t('There are no web page archive entities yet.'));

    // Simulate a cron run.
    web_page_archive_cron();

    // Assert files and directory no longer exist, but that the containing
    // directory still does exist (i.e. make sure we're only deleting our run).
    $this->assertFalse(file_exists($file_paths[0]));
    $this->assertFalse(file_exists($file_paths[1]));
    $this->assertFalse(file_exists('public://web-page-archive/wpa_html_capture/localhost'));
    $this->assertTrue(file_exists('public://web-page-archive/wpa_html_capture'));

  }

}
