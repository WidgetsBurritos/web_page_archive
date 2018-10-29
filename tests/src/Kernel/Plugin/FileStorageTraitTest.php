<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin;

use Drupal\web_page_archive\Plugin\FileStorageTrait;
use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;

/**
 * Tests FileStorageTrait functionality.
 *
 * @group web_page_archive
 */
class FileStorageTraitTest extends EntityStorageTestBase {

  use FileStorageTrait;

  /**
   * Tests FileStorageTrait::storagePath().
   */
  public function testStoragePath() {
    // Test a bunch of paths without a plugin ID.
    $this->assertEquals('public://web-page-archive', $this->storagePath());
    $this->assertEquals('public://web-page-archive/abc123', $this->storagePath('abc123'));
    $this->assertEquals('public://web-page-archive/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', $this->storagePath('abc123', '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60'));
    $this->assertEquals('public://web-page-archive/screenshots/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', $this->storagePath('abc123', '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', 'screenshots'));
    $this->assertEquals('public://web-page-archive/screenshots/abc123', $this->storagePath('abc123', NULL, 'screenshots'));
    $this->assertEquals('public://web-page-archive/screenshots/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', $this->storagePath(NULL, '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', 'screenshots'));
    $this->assertEquals('public://web-page-archive/screenshots', $this->storagePath(NULL, NULL, 'screenshots'));

    // Test a bunch of paths with a plugin ID.
    $this->pluginDefinition = ['id' => 'my-plugin'];
    $this->assertEquals('public://web-page-archive/my-plugin', $this->storagePath());
    $this->assertEquals('public://web-page-archive/my-plugin/abc123', $this->storagePath('abc123'));
    $this->assertEquals('public://web-page-archive/my-plugin/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', $this->storagePath('abc123', '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60'));
    $this->assertEquals('public://web-page-archive/screenshots/my-plugin/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', $this->storagePath('abc123', '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', 'screenshots'));
    $this->assertEquals('public://web-page-archive/screenshots/my-plugin/abc123', $this->storagePath('abc123', NULL, 'screenshots'));
    $this->assertEquals('public://web-page-archive/screenshots/my-plugin/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', $this->storagePath(NULL, '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', 'screenshots'));
    $this->assertEquals('public://web-page-archive/screenshots/my-plugin', $this->storagePath(NULL, NULL, 'screenshots'));
  }

  /**
   * Tests FileStorageTrait::getUniqueFileName().
   */
  public function testGetUniqueFileName() {
    $expected = [
      'public://web-page-archive/mydir/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60/abc-def--1.png',
      'public://web-page-archive/mydir/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60/abc-def--2.png',
      'public://web-page-archive/mydir/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60/abc-def--3.png',
      'public://web-page-archive/mydir/abc123/13e1f1ad-cd24-462b-95d7-a8da3b2d5a60/abc-def--4.png',
    ];
    foreach ($expected as $path) {
      $this->assertEquals($path, $this->getUniqueFileName('abc123', '13e1f1ad-cd24-462b-95d7-a8da3b2d5a60', 'Abc_def##%#@!&*^%&.', 'mydir', 'png'));
      touch($path);
    }
  }

}
