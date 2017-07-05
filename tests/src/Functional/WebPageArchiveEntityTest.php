<?php

namespace Drupal\Tests\web_page_archive\Functional;

use Drupal\Tests\BrowserTestBase;
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
    $this->session = $this->assertSession();
  }

  /**
   * Functional test of performance budget entity.
   */
  public function testPerformanceBudgetEntity() {
    $assert = $this->assertSession();

    // Login.
    $this->drupalLogin($this->authorizedUser);

    // Verify list exists with add button.
    $this->drupalGet('admin/config/development/web-page-archive');
    $this->assertLinkByHref('admin/config/development/web-page-archive/add');
    // Add an entity using the entity form.
    $this->drupalGet('admin/config/development/web-page-archive/add');
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

    // Verify entity view, edit, and delete buttons are present.
    // This is to ensure the entity config is correct for user operations.
    $this->assertLinkByHref('admin/config/development/web-page-archive/test_archive');
    $this->assertLinkByHref('admin/config/development/web-page-archive/test_archive/edit');
    $this->assertLinkByHref('admin/config/development/web-page-archive/test_archive/delete');

    // Verify previous values are retained.
    $this->drupalGet('admin/config/development/web-page-archive/test_archive/edit');
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

    // Verify previous values are retained.
    $this->drupalGet('admin/config/development/web-page-archive/test_archive/edit');
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
      'capture_html' => FALSE,
      'capture_screenshot' => TRUE,
    ];
    $wpa = \Drupal::entityManager()
      ->getStorage('web_page_archive')
      ->create($data);
    $wpa->save();

    // Login.
    $this->drupalLogin($this->authorizedUser);
    $this->drupalGet('admin/config/development/web-page-archive/programmatic_archive/edit');
    $this->assertResponse(Response::HTTP_OK);
    $this->assertFieldByName('label', 'Programmatic Archive');
    $this->assertFieldByName('sitemap_url', 'http://localhost/sitemap.xml');
    $this->assertFieldChecked('capture_screenshot');
    $this->assertNoFieldChecked('capture_html');

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
      'admin/config/development/web-page-archive',
      'admin/config/development/web-page-archive/add',
      'admin/config/development/web-page-archive/test_archive',
      'admin/config/development/web-page-archive/test_archive/edit',
      'admin/config/development/web-page-archive/test_archive/delete',
    ];
    foreach ($urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_FORBIDDEN);
    }
  }

}
