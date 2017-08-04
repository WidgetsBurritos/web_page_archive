<?php

namespace Drupal\web_page_archive\Cron;

use Cron\CronExpression;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\State\StateInterface;

/**
 * Runs cron tasks.
 */
class CronRunner {

  /**
   * The lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The state api service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The active request stack.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs an WebPageArchiveEditForm object.
   *
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state api service.
   * @param \Drupal\Component\Datetime\TimeInterface $datetime_time
   *   The datetime time service.
   */
  public function __construct(LockBackendInterface $lock, StateInterface $state, TimeInterface $datetime_time) {
    $this->lock = $lock;
    $this->state = $state;
    $this->time = $datetime_time;
  }

  /**
   * Runs the cron runner on the config entity.
   */
  public function run($config_entity) {
    $hasRan = FALSE;
    $id = $config_entity->id();
    $crontab = $config_entity->getCronSchedule();
    $timestamp = \date('Y-m-d H:i:s', $this->time->getRequestTime());
    if (!CronExpression::isValidExpression($crontab)) {
      throw new \Exception('Invalid crontab expression');
    }

    $lock_id = "web_page_archive_cron:{$id}";
    if ($this->lock->acquire($lock_id)) {
      $cron = CronExpression::factory($crontab);
      $next_run = $this->state->get("web_page_archive.next_run.{$id}", -1);
      if ($this->time->getRequestTime() >= $next_run) {
        if ($next_run >= 0 || $cron->isDue($timestamp)) {
          $hasRan = $config_entity->startNewRun();
        }
        $this->state->set("web_page_archive.next_run.{$id}", $cron->getNextRunDate()->format('U'));
      }
      $this->lock->release($lock_id);
    }

    return $hasRan;
  }

}
