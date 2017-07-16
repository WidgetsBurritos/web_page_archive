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
        'sitemap_url' => 'http://localhost/sitemap.xml',
        'capture_html' => FALSE,
        'capture_screenshot' => TRUE,
      ],
      t('Save')
    );
    $assert->pageTextContains('Created the Test Archive Web page archive entity.');

    // Verify entity has appropriate capture utilities.
    $entity = \Drupal::entityTypeManager()->getStorage('web_page_archive')->load('test_archive');
    $capture_utilities = $entity->getCaptureUtilities()->getConfiguration();
    $this->assertEqual(1, count($capture_utilities));
    $this->assertEqual('ScreenshotCaptureUtility', array_shift($capture_utilities)['id']);

    // Verify entity view, edit, and delete buttons are present.
    // This is to ensure the entity config is correct for user operations.
    $this->assertLinkByHref('admin/config/system/web-page-archive/test_archive');
    $this->assertLinkByHref('admin/config/system/web-page-archive/test_archive/edit');
    $this->assertLinkByHref('admin/config/system/web-page-archive/test_archive/delete');

    // Verify previous values are retained.
    $this->drupalGet('admin/config/system/web-page-archive/test_archive/edit');
    $this->assertFieldByName('sitemap_url', 'http://localhost/sitemap.xml');
    $this->assertFieldChecked('capture_screenshot');
    $this->assertNoFieldChecked('capture_html');

    // Update the new entity using the entity form.
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'Test Archiver',
        'sitemap_url' => 'http://localhost:1234/sitemap.xml',
        'capture_html' => TRUE,
        'capture_screenshot' => FALSE,
      ],
      t('Save')
    );
    $assert->pageTextContains('Saved the Test Archiver Web page archive entity.');

    // Verify entity has appropriate capture utilities.
    $entity = \Drupal::entityTypeManager()->getStorage('web_page_archive')->load('test_archive');
    $capture_utilities = $entity->getCaptureUtilities()->getConfiguration();
    $this->assertEqual(1, count($capture_utilities));
    $this->assertEqual('HtmlCaptureUtility', array_shift($capture_utilities)['id']);

    // Verify previous values are retained.
    $this->drupalGet('admin/config/system/web-page-archive/test_archive/edit');
    $this->assertFieldByName('sitemap_url', 'http://localhost:1234/sitemap.xml');
    $this->assertNoFieldChecked('capture_screenshot');
    $this->assertFieldChecked('capture_html');
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
      'sitemap_url' => 'http://localhost/sitemap.xml',
      'capture_html' => TRUE,
      'capture_screenshot' => TRUE,
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
    $this->assertFieldByName('sitemap_url', 'http://localhost/sitemap.xml');
    $this->assertFieldChecked('capture_screenshot');
    $this->assertFieldChecked('capture_html');

    // Verify entity has appropriate capture utilities.
    $entity = \Drupal::entityTypeManager()->getStorage('web_page_archive')->load('programmatic_archive');
    $capture_utilities = $entity->getCaptureUtilities()->getConfiguration();
    $this->assertEqual(2, count($capture_utilities));
    $this->assertEqual('HtmlCaptureUtility', array_shift($capture_utilities)['id']);
    $this->assertEqual('ScreenshotCaptureUtility', array_shift($capture_utilities)['id']);
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
      'sitemap_url' => 'http://localhost/sitemap.xml',
      'capture_html' => FALSE,
      'capture_screenshot' => TRUE,
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
      'sitemap_url' => 'http://localhost/sitemap.xml',
      'capture_html' => TRUE,
      'capture_screenshot' => TRUE,
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
    $this->assertEqual(0, $queue->numberOfItems());

    // Queue up items.
    $entity->startNewRun($handler);

    // Check queue.
    $this->assertEqual(4, $queue->numberOfItems());
  }

}
