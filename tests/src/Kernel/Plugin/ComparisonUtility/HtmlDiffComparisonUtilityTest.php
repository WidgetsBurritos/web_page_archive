<?php

namespace Drupal\Tests\web_page_archive\Kernel\Plugin\ComparisonUtility;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\wpa_html_capture\Plugin\CaptureResponse\HtmlCaptureResponse;

/**
 * Tests the functionality of the html diff comparison utility.
 *
 * @group web_page_archive
 */
class HtmlDiffComparisonUtilityTest extends EntityKernelTestBase {

  protected $htmlDiffComparisonUtility;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'web_page_archive',
    'wpa_html_capture',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $comparison_utility_manager = $this->container->get('plugin.manager.comparison_utility');
    $this->htmlDiffComparisonUtility = $comparison_utility_manager->createInstance('wpa_html_diff_compare');
  }

  /**
   * Tests HtmlDiffComparisonUtility::compare().
   */
  public function testCompare() {
    $file1 = __DIR__ . '/../../fixtures/sample1.html';
    $file2 = __DIR__ . '/../../fixtures/sample2.html';

    // Assert same file returns CompareResponseCollection containing
    // SameCompareResponse object.
    $capture1 = new HtmlCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new HtmlCaptureResponse($file1, 'http://www.drupal.org/');
    $response = $this->htmlDiffComparisonUtility->compare($capture1, $capture2);
    $this->assertEquals('Drupal\web_page_archive\Plugin\CompareResponse\SameCompareResponse', get_class($response));
    $this->assertEquals(0, $response->getVariance());

    // Assert files have 0.6% html diff variance.
    $capture1 = new HtmlCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new HtmlCaptureResponse($file2, 'http://www.drupal.org/');
    $response = $this->htmlDiffComparisonUtility->compare($capture1, $capture2);
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($response));
    $this->assertEquals(10.0, $response->getVariance());

    // Assert files have 10% html diff variance (reversed order).
    $capture1 = new HtmlCaptureResponse($file1, 'http://www.drupal.org/');
    $capture2 = new HtmlCaptureResponse($file2, 'http://www.drupal.org/');
    $response = $this->htmlDiffComparisonUtility->compare($capture2, $capture1);
    $this->assertEquals('Drupal\wpa_html_capture\Plugin\CompareResponse\HtmlVarianceCompareResponse', get_class($response));
    $this->assertEquals(10.0, $response->getVariance());
  }

  /**
   * Tests HtmlDiffComparisonUtility::getFilterCriteria().
   */
  public function testGetFilterCriteria() {
    $expected = ['wpa_html_variance_compare_response' => 'HTML: Diff'];
    $this->assertEquals($expected, $this->htmlDiffComparisonUtility->getFilterCriteria());
  }

}
