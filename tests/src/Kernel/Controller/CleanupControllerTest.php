<?php

namespace Drupal\Tests\web_page_archive\Kernel\Controller;

use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;

/**
 * Tests the functionality of the cleanup controller.
 *
 * @group web_page_archive
 */
class CleanupControllerTest extends EntityStorageTestBase {

  /**
   * Tests RunComparisonController::deleteOldRevisionsByDays().
   */
  public function testDeleteOldRevisionsByDays() {
    // Start 5 days ago.
    $start_time = $this->container->get('datetime.time')->getCurrentTime() - 5 * 86400;
    $urls = ['https://www.homestarrunner.com'];

    // Create a config entity with revisions incrementing by 24 hours.
    $web_page_archive = $this->getWpaEntity('Some job', $urls, 5, $start_time, 86400);

    // Set our retention settings to 3 days.
    $web_page_archive->set('retention_type', 'days');
    $web_page_archive->set('retention_value', '3');
    $web_page_archive->save();

    // Confirm 5 additional revisions were created (i.e. 6 total).
    $revision_ids = $web_page_archive->getRevisionIds();
    $this->assertEquals(6, count($revision_ids));

    // Lock a revision that would get removed otherwise.
    $locked_run = $this->runStorage->loadRevision($revision_ids[1]);
    $locked_run->setRetentionLocked(TRUE);
    $locked_run->save();

    // Lock another revision that would not get removed.
    $locked_run = $this->runStorage->loadRevision($revision_ids[5]);
    $locked_run->setRetentionLocked(TRUE);
    $locked_run->save();

    // Process the retention plan.
    $web_page_archive->processRetentionPlan();

    // There were originally 6 revisions:
    // - 5 days ago
    // - 4 days ago (locked)
    // - 3 days ago
    // - 2 days ago
    // - 1 days ago (locked)
    // - 0 days ago
    // Any revisions 3 days or older should be removed so 6-3=3.
    // However one of those revisions is locked, so we can only remove 2.
    // Thus the expectation is 6-2=4.
    $revision_ids = $web_page_archive->getRevisionIds();
    $this->assertEquals(4, count($revision_ids));
  }

  /**
   * Tests RunComparisonController::deleteOldRevisionsByRevisions().
   */
  public function testDeleteOldRevisionsByRevisions() {
    // Start 5 days ago.
    $start_time = $this->container->get('datetime.time')->getCurrentTime() - 3 * 86400;
    $urls = ['https://www.homestarrunner.com'];

    // Create a config entity with revisions incrementing by 12 hours.
    $web_page_archive = $this->getWpaEntity('Some job', $urls, 5, $start_time, 43200);

    // Set our retention settings to 5 revisions.
    $web_page_archive->set('retention_type', 'revisions');
    $web_page_archive->set('retention_value', '3');
    $web_page_archive->save();

    // Confirm 10 additional revisions were created (i.e. 11 total).
    $revision_ids = $web_page_archive->getRevisionIds();
    $this->assertEquals(6, count($revision_ids));

    // Lock a revision that would get removed otherwise.
    $locked_run = $this->runStorage->loadRevision($revision_ids[1]);
    $locked_run->setRetentionLocked(TRUE);
    $locked_run->save();

    // Lock another revision that would not get removed.
    $locked_run = $this->runStorage->loadRevision($revision_ids[5]);
    $locked_run->setRetentionLocked(TRUE);
    $locked_run->save();

    // Process the retention plan.
    $web_page_archive->processRetentionPlan();

    // There were originally 6 revisions:
    // - 60 hours ago
    // - 48 hours ago (locked)
    // - 36 hours ago
    // - 24 hours ago
    // - 12 hours ago (locked)
    // - 0 hours ago
    // We're only interested in the 3 most recent revisions.
    // But the revision from 48 hours ago is locked, so it takes place of the
    // revision from 24 hours ago.
    $expected = [
      $revision_ids[1],
      $revision_ids[4],
      $revision_ids[5],
    ];
    $revision_ids = $web_page_archive->getRevisionIds();
    $this->assertEquals(3, count($revision_ids));

    $this->assertEquals($expected, $revision_ids);
  }

  /**
   * Tests that nothing is removed if using default settings.
   */
  public function testDefaultSettingsDontDeleteAnything() {
    // Start 5 days ago.
    $start_time = $this->container->get('datetime.time')->getCurrentTime() - 3 * 86400;
    $urls = ['https://www.homestarrunner.com'];

    // Create a config entity with revisions incrementing by 12 hours.
    $web_page_archive = $this->getWpaEntity('Some job', $urls, 5, $start_time, 43200);

    // Confirm 10 additional revisions were created (i.e. 11 total).
    $revision_ids = $web_page_archive->getRevisionIds();
    $this->assertEquals(6, count($revision_ids));

    // Process the retention plan.
    $web_page_archive->processRetentionPlan();

    $revision_ids = $web_page_archive->getRevisionIds();
    $this->assertEquals(6, count($revision_ids));
  }

}
