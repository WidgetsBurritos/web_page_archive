<?php

namespace Drupal\Tests\web_page_archive\Functional;

use Drupal\Tests\BrowserTestBase;

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
   * User.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

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
    $this->user = $this->drupalCreateUser([
      'administer web page archive',
    ]);
  }

  /**
   * Functional test of web page archive entity.
   */
  public function testWebPageArchiveEntity() {
    $assert = $this->assertSession();
    // Login.
    $this->drupalLogin($this->user);

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
      ],
      t('Save')
    );
    $assert->pageTextContains('The Test Archive web page archive was saved.');

    // Verify entity edit, disable, and delete buttons are present.
    // This is to ensure the entity config is correct for user operations.
    $this->assertLinkByHref('admin/config/development/web-page-archive/test_archive');
    $this->assertLinkByHref('admin/config/development/web-page-archive/test_archive/disable');
    $this->assertLinkByHref('admin/config/development/web-page-archive/test_archive/delete');

    // Update the new entity using the entity form.
    $this->drupalGet('admin/config/development/web-page-archive/test_archive');
    $this->drupalPostForm(
      NULL,
      [
        'label' => 'Test Archive2',
      ],
      t('Save')
    );
    $assert->pageTextContains('The Test Archive2 web page archive was saved.');
  }

}
