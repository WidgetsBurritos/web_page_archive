<?php

namespace Drupal\Tests\web_page_archive\Unit\Plugin\views\field;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Plugin\CaptureResponse\UriCaptureResponse;
use Drupal\web_page_archive\Plugin\views\field\SerializedCapture;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Plugin\views\field\SerializedCapture
 *
 * @group web_page_archive
 */
class SerializedCaptureTest extends UnitTestCase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->plugin = $this->getMockBuilder(SerializedCapture::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMock();
    $this->plugin->setStringTranslation($this->getStringTranslationStub());
    $this->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Tests Drupal\web_page_archive\Plugin\views\field\SerializedCapture::doRenderSerialized().
   */
  public function testEmptySerializedArrayDoesNothing() {
    $serialized = 'a:0:{}';
    $expected = NULL;
    $this->assertEquals($expected, $this->plugin->doRenderSerialized($serialized));
  }

  /**
   * Tests Drupal\web_page_archive\Plugin\views\field\SerializedCapture::doRenderSerialized().
   */
  public function testNonExistentClassOutputsGracefulErrorResponse() {
    $serialized = 'a:1:{s:16:"capture_response";O:12:"DoesNotExist":1:{s:1:"x";s:3:"abc";}};';
    $expected = [
      '#markup' => $this->t('Invalid Capture Response Object: @class', ['@class' => 'DoesNotExist']),
    ];
    $this->assertEquals($expected, $this->plugin->doRenderSerialized($serialized));
  }

  /**
   * Tests Drupal\web_page_archive\Plugin\views\field\SerializedCapture::doRenderSerialized().
   */
  public function testExistingClassOutputsExpectedResponse() {
    $response = [
      'capture_response' => new UriCaptureResponse('This is my response', 'https://www.google.com'),
    ];
    $serialized = serialize($response);
    $expected = 'This is my response';
    $this->assertEquals($expected, $this->plugin->doRenderSerialized($serialized));
  }

}
