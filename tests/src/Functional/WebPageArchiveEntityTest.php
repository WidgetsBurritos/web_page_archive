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
   * Authorized User.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authorizedUser;

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
    $this->authorizedUser = $this->drupalCreateUser([
      'administer web page archive',
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
    $this->drupalLogin($this->authorizedUser);

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
        'cron_schedule' => '* * * * *',
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

    // Update the new entity using the entity form.
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'Test Archiver',
        'timeout' => 250,
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
      'cron_schedule' => '* * * * *',
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
    $wpa = \Drupal::entityManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Login.
    $this->drupalLogin($this->authorizedUser);
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
   * Functional test to ensure authorized access only.
   */
  public function testUnauthorizedUser() {
    $assert = $this->assertSession();

    // Create a dummy entity.
    $data = [
      'label' => 'Test Archive',
      'id' => 'test_archive',
      'cron_schedule' => '* * * * *',
      'timeout' => 500,
      'url_type' => 'sitemap',
      'urls' => 'http://localhost/sitemap.xml',
    ];
    $wpa = \Drupal::entityManager()
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
      'cron_schedule' => '* * * * *',
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
    $wpa = \Drupal::entityManager()
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
    $this->drupalLogin($this->authorizedUser);

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
    $file_path = str_replace(['://', ':', '/'], '-', $capture_url);
    $assert->responseMatches("/<span>.*{$file_path}\.html<\/span>/");
  }

}
