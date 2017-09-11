<?php

namespace Drupal\web_page_archive\Entity;

use Cron\CronExpression;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of Web page archive entity entities.
 */
class WebPageArchiveListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $capture_utilities = \Drupal::service('plugin.manager.capture_utility')->getDefinitions();
    if (empty($capture_utilities)) {
      $url = Url::fromRoute('system.modules_list', [], ['fragment' => 'edit-modules-web-page-archive']);
      $link = \Drupal::l($this->t('install a capture utility module'), $url);
      \drupal_set_message($this->t('You have installed Web Page Archive, but do not have any capture utilities installed. You will need to @install before you use this module.', ['@install' => $link]), 'warning');
    }
    return parent::load();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Web page archive entity');
    $header['id'] = $this->t('Machine name');
    $header['runs'] = $this->t('Runs');
    $header['schedule'] = $this->t('Schedule');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['runs'] = $this->formatPlural($entity->getRunCt(), '1 run', '@count runs');

    // Output job schedule.
    if ($entity->getUseCron() && CronExpression::isValidExpression($entity->getCronSchedule())) {
      $cron = CronExpression::factory($entity->getCronSchedule());
      $row['schedule'] = $this->t('Next run: @next_run', ['@next_run' => $cron->getNextRunDate()->format('Y-m-d @ g:ia T')]);
    }
    else {
      $row['schedule'] = $this->t('Never');
    }

    return $row + parent::buildRow($entity);
  }

}
