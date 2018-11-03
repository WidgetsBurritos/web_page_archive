<?php

namespace Drupal\web_page_archive\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\web_page_archive\Plugin\FileStorageTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Downloads a web page archive run.
 */
class DownloadRunForm extends FormBase {

  use FileStorageTrait;

  /**
   * Constructs a new DownloadRunForm.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(DateFormatterInterface $date_formatter, FileSystemInterface $file_system) {
    $this->dateFormatter = $date_formatter;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_page_archive_download_run';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $web_page_archive_run_revision = NULL) {
    $this->runRevision = $web_page_archive_run_revision;
    $form['intro'] = [
      '#prefix' => '<div class="download-run-intro">',
      '#markup' => $this->t('You can download all images from the specified run as a *.zip file.'),
      '#suffix' => '</div>',
    ];

    $form['button'] = [
      '#prefix' => '<div class="download-run-button">',
      '#type' => 'submit',
      '#value' => $this->t('Download Run'),
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Prepare zip file.
    $zip = new \ZipArchive();
    $filename = $this->downloadTitle($this->runRevision);
    $zip_file = $this->getUniqueFileName(NULL, NULL, $filename, 'tmp', 'zip');
    $real_zip_file = $this->fileSystem->realpath($zip_file);
    $zip->open($real_zip_file, \ZipArchive::CREATE);

    // Attach all captures.
    $captured = $this->runRevision->getCapturedArray();
    foreach ($this->parseCapturedList($captured) as $capture) {
      $file_name = $this->fileSystem->basename($capture['file']);
      $real_path = $this->fileSystem->realpath($capture['file']);
      $zip->addFile($real_path, $file_name);
      $summary[] = [$capture['url'], $file_name];
    }

    // Generate and attach summary.
    if ($summary_file = $this->generateSummaryFile($summary)) {
      $zip->addFile($summary_file, 'summary.csv');
    }

    // Close zip file and send response.
    $zip->close();
    clearstatcache(FALSE, $zip_file);
    $response = new BinaryFileResponse($zip_file);
    $response->trustXSendfileTypeHeader();
    $response->headers->set('Content-Type', 'application/zip');
    $response->setContentDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $this->fileSystem->basename($zip_file)
    );
    $response->deleteFileAfterSend(TRUE);
    $response->send();

    return $response;
  }

  /**
   * Parses the captured array for individual capture results.
   */
  private function parseCapturedList(FieldItemList $captured) {
    foreach ($captured as $capture) {
      $unserialized = unserialize($capture->getString());
      if (get_class($unserialized['capture_response']) !== '__PHP_Incomplete_Class') {
        yield [
          'url' => $unserialized['capture_response']->getCaptureUrl(),
          'file' => $unserialized['capture_response']->getContent(),
        ];
      }
    }
  }

  /**
   * Generates a summary file based on a summary report array.
   */
  private function generateSummaryFile(array $summary) {
    $summary_file = $this->getUniqueFileName(NULL, NULL, 'summary', 'tmp', 'csv');
    $summary_path = $this->fileSystem->realpath($summary_file);
    $fh = fopen($summary_path, 'w');
    fputcsv($fh, [$this->t('Url'), $this->t('File')]);
    foreach ($summary as $row) {
      fputcsv($fh, $row);
    }
    fclose($fh);
    return $summary_path;
  }

  /**
   * Generates the title for the download page.
   */
  public function downloadTitle($web_page_archive_run_revision) {
    $label = $web_page_archive_run_revision->label();
    $date = $this->dateFormatter->format($web_page_archive_run_revision->getRevisionCreationTime(), 'custom', 'Y-m-d H:i:s');
    return "{$label}: {$date}";
  }

}
