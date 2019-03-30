<?php

namespace Drupal\Tests\web_page_archive\Kernel\Form;

use Drupal\Core\Form\FormState;
use Drupal\Tests\web_page_archive\Kernel\EntityStorageTestBase;
use Drupal\web_page_archive\Form\WebPageArchiveQueueForm;

/**
 * Tests web page archive queue form.
 *
 * @group web_page_archive
 */
class WebPageArchiveQueueFormTest extends EntityStorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'web_page_archive',
    'wpa_skeleton_capture',
  ];

  /**
   * Tests that batch_set is called only once.
   */
  public function testBatchIsOnlySetOnce() {
    $web_page_archive = $this->getWpaEntity('abc', ['http://localhost']);
    $form = WebPageArchiveQueueForm::create($this->container);
    $form_state = new FormState();
    $form->setEntity($web_page_archive);
    $form->startRun([], $form_state);
    $x = batch_get();
    $this->assertEquals(1, count($x['sets']));
  }

}
