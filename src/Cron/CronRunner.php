<?php

namespace Drupal\web_page_archive\Cron;

use Cron\CronExpression;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\State\StateInterface;
use Drupal\web_page_archive\Controller\WebPageArchiveController;

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
    $id = $config_entity->id();
    $crontab = $config_entity->getCronSchedule();
    $timestamp = \date('Y-m-d H:i:s', $this->time->getRequestTime());
    if (!CronExpression::isValidExpression($crontab)) {
      throw new \Exception('Invalid crontab expression');
    }

    // Attempt to acquire lock.
    $lock_id = "web_page_archive_cron:{$id}";
    if (!$this->lock->acquire($lock_id)) {
      return FALSE;
    }

    // Check cron window.
    $cron = CronExpression::factory($crontab);
    $next_run = $this->state->get("web_page_archive.next_run.{$id}", -1);

    if ($this->time->getRequestTime() < $next_run || $next_run < 0 && !$cron->isDue($timestamp)) {
      $this->lock->release($lock_id);
      return FALSE;
    }

    // Attempt to start a new run.
    if (!$config_entity->startNewRun()) {
      $this->lock->release($lock_id);
      return FALSE;
    }

    // Process the queue.
    $success_ct = $fail_ct = 0;
    while ($success_ct + $fail_ct < $config_entity->getQueueCt()) {
      if (WebPageArchiveController::batchProcess($config_entity)) {
        $success_ct++;
      }
      else {
        $fail_ct++;
      }
    }

    // Set messages.
    if ($success_ct > 0) {
      \drupal_set_message(\t('Processed @count URLs.', ['@count' => $success_ct]), 'status');
    }
    if ($fail_ct > 0) {
      \drupal_set_message(\t('Failed to process @count URLs.', ['@count' => $success_ct]), 'error');
    }

    $this->state->set("web_page_archive.next_run.{$id}", $config_entity->calculateNextRun());
    $this->lock->release($lock_id);

    return TRUE;
  }

}
