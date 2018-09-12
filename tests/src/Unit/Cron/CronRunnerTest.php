<?php

namespace Drupal\Tests\web_page_archive\Unit\Cron;

use Drupal\Tests\UnitTestCase;
use Drupal\web_page_archive\Cron\CronRunner;

/**
 * @coversDefaultClass \Drupal\web_page_archive\Cron\CronRunner
 *
 * @group web_page_archive
 */
class CronRunnerTest extends UnitTestCase {

  /**
   * Helper function to get a mock web page archive with pre-defined crontab.
   */
  private function getMockWebPageArchive($crontab = NULL) {
    $mock_web_page_archive = $this->getMockBuilder('\Drupal\web_page_archive\Entity\WebPageArchive')
      ->disableOriginalConstructor()
      ->getMock();
    $mock_web_page_archive->expects($this->any())
      ->method('id')
      ->will($this->returnValue('abc123'));
    $mock_web_page_archive->expects($this->any())
      ->method('getCronSchedule')
      ->will($this->returnValue($crontab));
    $mock_web_page_archive->expects($this->any())
      ->method('getUseCron')
      ->will($this->returnValue(isset($crontab)));

    return $mock_web_page_archive;
  }

  /**
   * Helper function to get a mock time service.
   */
  private function getMockTime($timestamp = '1501774086') {
    $mock_time = $this->getMockBuilder('\Drupal\Component\Datetime\TimeInterface')
      ->getMock();
    $mock_time->expects($this->any())
      ->method('getRequestTime')
      ->will($this->returnValue($timestamp));

    return $mock_time;
  }

  /**
   * Helper function to get a mock lock service.
   */
  private function getMockLock($acquire = TRUE) {
    // Setup mock lock service.
    $mock_lock = $this->getMockBuilder('\Drupal\Core\Lock\LockBackendInterface')
      ->getMock();
    $mock_lock->expects($this->any())
      ->method('acquire')
      ->will($this->returnValue($acquire));
    $mock_lock->expects($this->any())
      ->method('release');

    return $mock_lock;
  }

  /**
   * Helper function to get a mock state service.
   */
  private function getMockState() {
    $mock_state = $this->getMockBuilder('\Drupal\Core\State\StateInterface')
      ->getMock();

    return $mock_state;
  }

  /**
   * Helper function to get a config factory service.
   */
  private function getMockConfigFactory() {
    $mock_config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
      ->disableOriginalConstructor()
      ->getMock();

    $mock_config = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
      ->disableOriginalConstructor()
      ->getMock();

    $mock_config->expects($this->any())
      ->method('get')
      ->will($this->returnValue(25));

    $mock_config_factory->expects($this->any())
      ->method('get')
      ->will($this->returnValue($mock_config));

    return $mock_config_factory;
  }

  /**
   * Helper function to get a mock messenger service.
   */
  private function getMockMessenger() {
    $mock_messenger = $this->getMockBuilder('\Drupal\Core\Messenger\MessengerInterface')
      ->getMock();

    return $mock_messenger;
  }

  /**
   * Helper function to get a cron runner based on default or supplied mocks.
   */
  private function getCronRunner(array $options = []) {
    if (empty($options['mock_time'])) {
      $options['mock_time'] = $this->getMockTime();
    }
    if (empty($options['mock_lock'])) {
      $options['mock_lock'] = $this->getMockLock();
    }
    if (empty($options['mock_state'])) {
      $options['mock_state'] = $this->getMockState();
    }
    if (empty($options['mock_config_factory'])) {
      $options['mock_config_factory'] = $this->getMockConfigFactory();
    }
    if (empty($options['mock_messenger'])) {
      $options['mock_messenger'] = $this->getMockMessenger();
    }

    return new CronRunner($options['mock_lock'], $options['mock_state'], $options['mock_time'], $options['mock_config_factory'], $options['mock_messenger']);
  }

  /**
   * Assures new entities, run when it is time.
   */
  public function testNewEntitiesRunWhenItIsTime() {
    $mock_state = $this->getMockState();
    $mock_state->expects($this->once())
      ->method('get')
      ->will($this->returnValue(-1));

    // Setup web page archive responses.
    $mock_web_page_archive = $this->getMockWebPageArchive('28 1 * * *');
    $mock_web_page_archive->expects($this->once())
      ->method('startNewRun')
      ->will($this->returnValue(TRUE));

    $cron_runner = $this->getCronRunner(['mock_state' => $mock_state]);
    $this->assertTrue($cron_runner->run($mock_web_page_archive));
  }

  /**
   * Assures new entities, not inside the scheduled cron window don't run.
   */
  public function testNewEntitiesDontRunUntilTime() {
    $mock_state = $this->getMockState();
    $mock_state->expects($this->once())
      ->method('get')
      ->will($this->returnValue(-1));

    // Setup web page archive responses.
    $cron_runner = $this->getCronRunner(['mock_state' => $mock_state]);
    $mock_web_page_archive = $this->getMockWebPageArchive('30 * * * *');
    $this->assertFalse($cron_runner->run($mock_web_page_archive));
  }

  /**
   * Tests that a cron won't run twice in the same minute.
   */
  public function testCronTabOnlyRunsOneTimePerMinute() {
    // Setup cron runner.
    // Important: at() is index based on all method calls on the mock, not just
    // the specified method. That means, that ->set() calls count against this
    // index. This could potentially cause volatility in these tests in the
    // future. We should look into a more steady solution in the future.
    $mock_state = $this->getMockState();
    $mock_state->expects($this->at(0))
      ->method('get')
      ->will($this->returnValue('0'));
    $mock_state->expects($this->at(2))
      ->method('get')
      ->will($this->returnValue('1501774146'));

    $cron_runner = $this->getCronRunner(['mock_state' => $mock_state]);

    $mock_web_page_archive = $this->getMockWebPageArchive('* * * * *');

    // Affirm run() only completes once.
    $mock_web_page_archive->expects($this->once())
      ->method('startNewRun')
      ->will($this->returnValue(TRUE));
    $this->assertTrue($cron_runner->run($mock_web_page_archive));
    $this->assertFalse($cron_runner->run($mock_web_page_archive));
  }

  /**
   * Tests invalid crontab throws an exception.
   *
   * @expectedException Exception
   * @expectedExceptionMessage Invalid crontab expression
   */
  public function testInvalidCronTabThrowsException() {
    $mock_web_page_archive = $this->getMockWebPageArchive('I am a bad bad crontab.');
    $cron_runner = $this->getCronRunner();
    $cron_runner->run($mock_web_page_archive);
  }

  /**
   * Tests lock failure prevents run.
   */
  public function testLockAcquireFailurePreventsRun() {
    $mock_web_page_archive = $this->getMockWebPageArchive('* * * * *');
    $mock_lock = $this->getMockLock(FALSE);
    $cron_runner = $this->getCronRunner(['mock_lock' => $mock_lock]);
    $this->assertFalse($cron_runner->run($mock_web_page_archive));
  }

  /**
   * Tests config entities with cron disabled don't run.
   */
  public function testUnscheduledConfigEntityPreventsRun() {
    $mock_web_page_archive = $this->getMockWebPageArchive();
    $cron_runner = $this->getCronRunner();
    $this->assertFalse($cron_runner->run($mock_web_page_archive));
  }

  /**
   * Tests capture max is pulled from configuration.
   */
  public function testCaptureMaxReturnsProperValue() {
    $cron_runner = $this->getCronRunner();
    $this->assertEquals(25, $cron_runner->getCaptureMax());
  }

}
