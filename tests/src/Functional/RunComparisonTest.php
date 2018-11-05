<?php

namespace Drupal\Tests\web_page_archive\Functional;

use Drupal\web_page_archive\Plugin\CompareResponse\CompareResponseCollection;
use Drupal\web_page_archive\Plugin\CompareResponse\FileSizeVarianceCompareResponse;
use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests web page archive.
 *
 * @group web_page_archive
 */
class RunComparisonTest extends BrowserTestBase {

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
    $this->runStorage = \Drupal::service('entity_type.manager')->getStorage('web_page_archive_run');
    $this->comparisonStorage = \Drupal::service('entity_type.manager')->getStorage('wpa_run_comparison');
  }

  /**
   * Tests that a new comparison shows row results on summary page.
   */
  public function testComparisonHasRow() {
    $assert = $this->assertSession();

    // Create dummy run and comparison entities.
    $run1 = $this->runStorage->create(['success_ct' => 2]);
    $run1->save();
    $run2 = $this->runStorage->create(['success_ct' => 1]);
    $run2->save();

    $data = [
      'name' => 'Really special comparison',
      'run1' => $run1->id(),
      'run2' => $run2->id(),
      'strip_type' => '',
      'strip_patterns' => '',
    ];
    $comparison = $this->comparisonStorage->create($data);
    $comparison->save();

    $response_collection = new CompareResponseCollection();
    $response_collection->addResponse(new FileSizeVarianceCompareResponse(23.3));

    $data = [
      'delta1' => 1,
      'delta2' => 2,
      'has_left' => 1,
      'has_right' => 1,
      'langcode' => 'es',
      'results' => serialize([]),
      'run1' => $run1->id(),
      'run2' => $run2->id(),
      'url' => 'http://www.zombo.com',
      'variance' => 23.3,
      'compare_response' => $response_collection,
    ];
    $this->comparisonStorage->addResult($comparison, $data);
    $this->comparisonStorage->addNormalizedVariance([
      'cid' => $comparison->id(),
      'response_index' => 0,
      'plugin_id' => FileSizeVarianceCompareResponse::getId(),
      'variance' => 23.3,
    ]);

    $response_collection = new CompareResponseCollection();
    $data = [
      'delta1' => 2,
      'delta2' => 3,
      'has_left' => 1,
      'has_right' => 1,
      'langcode' => 'es',
      'results' => serialize([]),
      'run1' => $run1->id(),
      'run2' => $run2->id(),
      'url' => 'http://www.homestarrunner.com',
      'variance' => -1,
      'compare_response' => $response_collection,
    ];
    $this->comparisonStorage->addResult($comparison, $data);
    $this->comparisonStorage->addNormalizedVariance([
      'cid' => $comparison->id(),
      'response_index' => 1,
      'plugin_id' => FileSizeVarianceCompareResponse::getId(),
      'variance' => -1,
    ]);

    // Login.
    $this->drupalLogin($this->authorizedAdminUser);
    $this->drupalGet("admin/config/system/web-page-archive/compare/{$comparison->id()}");

    $assert->pageTextContains('Really special comparison');
    $assert->pageTextContains('Operator');
    $assert->pageTextContains('URL');
    $assert->pageTextContains('Exists in Run #1?');
    $assert->pageTextContains('Exists in Run #2?');
    $assert->pageTextContains('Variance Type');
    $assert->pageTextContains('File: Size');
    $assert->pageTextContains('Is greater than or equal to');
    $assert->pageTextContains('Variance: 23.3%');
    $assert->pageTextNotContains('Variance: -1%');

    /*
     * TODO: This will change once UriCaptureResponse provides compare().
     *
     * @see https://www.drupal.org/project/web_page_archive/issues/2932533
     */
    $assert->pageTextContains('Could not render results');
  }

  /**
   * Functional test to ensure authorized admin access.
   */
  public function testAdminUser() {
    $assert = $this->assertSession();

    // Create a dummy entity.
    $data = [];
    $comparison = $this->comparisonStorage->create($data);
    $comparison->save();

    // Login.
    $this->drupalLogin($this->authorizedAdminUser);

    $urls = [
      'admin/config/system/web-page-archive/compare',
      'admin/config/system/web-page-archive/compare/history',
      'admin/config/system/web-page-archive/compare/runs/json',
      "admin/config/system/web-page-archive/compare/{$comparison->id()}",
      "admin/config/system/web-page-archive/compare/{$comparison->id()}/delete",
    ];
    foreach ($urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_OK);
    }
  }

  /**
   * Functional test to ensure authorized read-only access.
   */
  public function testReadOnlyUser() {
    $assert = $this->assertSession();

    // Create a dummy entity.
    $data = [];
    $comparison = $this->comparisonStorage->create($data);
    $comparison->save();

    // Login.
    $this->drupalLogin($this->authorizedReadOnlyUser);

    $permitted_urls = [
      'admin/config/system/web-page-archive/compare',
      'admin/config/system/web-page-archive/compare/history',
      "admin/config/system/web-page-archive/compare/{$comparison->id()}",
    ];
    foreach ($permitted_urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_OK);
    }

    $forbidden_urls = [
      "admin/config/system/web-page-archive/compare/{$comparison->id()}/delete",
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
    $data = [];
    $comparison = $this->comparisonStorage->create($data);
    $comparison->save();

    // Login.
    $this->drupalLogin($this->unauthorizedUser);

    $urls = [
      'admin/config/system/web-page-archive/compare',
      'admin/config/system/web-page-archive/compare/history',
      "admin/config/system/web-page-archive/compare/{$comparison->id()}",
      "admin/config/system/web-page-archive/compare/{$comparison->id()}/delete",
    ];
    foreach ($urls as $url) {
      $this->drupalGet($url);
      $this->assertResponse(Response::HTTP_FORBIDDEN);
    }
  }

}
