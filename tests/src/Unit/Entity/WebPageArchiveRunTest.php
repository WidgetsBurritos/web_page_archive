<?php

namespace Drupal\Tests\web_page_archive\Unit\Entity;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Entity\WebPageArchiveRun
 *
 * @group web_page_archive
 */
class WebPageArchiveRunTest extends UnitTestCase {

  /**
   * Mock WebPageArchiveRun object.
   *
   * @var \Drupal\web_page_archive\Entity\WebPageArchiveRun
   */
  protected $runEntity;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->runEntity = $this->getMockBuilder('\Drupal\web_page_archive\Entity\WebPageArchiveRun')
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();
  }

  /**
   * Retrieves mock FieldItemList object that returns a string value.
   *
   * @param mixed $retval
   *   Return value for getString() method.
   *
   * @return \Drupal\Core\Field\FieldItemList
   *   Mock object.
   */
  private function getMockFieldItemList($retval) {
    $field_item_list = $this->getMockBuilder('\Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();
    $field_item_list->expects($this->once())
      ->method('getString')
      ->will($this->returnValue($retval));
    return $field_item_list;
  }

  /**
   * Tests scenario when run_uuid is not empty.
   */
  public function testRetrievesCorrectValueWhenRunUuidIsSet() {
    $uuid = '12345678-1234-4321-1234-123456789012';
    $mock_uuid_list = $this->getMockFieldItemList($uuid);
    $this->runEntity->expects($this->once())
      ->method('get')
      ->will($this->returnValue($mock_uuid_list));
    $this->assertEquals($uuid, $this->runEntity->getRunUuid());
  }

  /**
   * Tests scenario when run_uuid is empty, but revision_log is not.
   */
  public function testRetrievesCorrectValueWhenRevisionLogIsSet() {
    $uuid = '12345678-aaaa-bbbb-cccc-123456789012';
    $revision_log = "Name: Some Job -- Run ID: {$uuid} -- Queue Ct: 15";

    // Mock the $this->get('run_uuid') response:
    $mock_uuid_list = $this->getMockFieldItemList(NULL);

    // Mock the $this->get('revision_log') response:
    $mock_log_list = $this->getMockFieldItemList($revision_log);

    // First time get() should return NULL.
    $this->runEntity->expects($this->exactly(2))
      ->method('get')
      ->will($this->onConsecutiveCalls($mock_uuid_list, $mock_log_list));
    $this->assertEquals($uuid, $this->runEntity->getRunUuid());
  }

  /**
   * Tests scenario when run_uuid and revision_log are both empty.
   */
  public function testRetrievesNullWhenNoValueIsSet() {
    // Mock the $this->get('run_uuid') response:
    $mock_uuid_list = $this->getMockFieldItemList(NULL);

    // Mock the $this->get('revision_log') response:
    $mock_log_list = $this->getMockFieldItemList(NULL);

    // First time get() should return NULL.
    $this->runEntity->expects($this->exactly(2))
      ->method('get')
      ->will($this->onConsecutiveCalls($mock_uuid_list, $mock_log_list));
    $this->assertNull($this->runEntity->getRunUuid());
  }

}
