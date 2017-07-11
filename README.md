# Web Page Archive

The Web Page Archive module allows you to use Drupal to perform periodic
snapshots on local and remote websites based on a specified XML sitemap.

This project is currently under active development and is still in early
alpha stages.

## Requirements

- Drupal 8.x
- PHP 5.6+
- [ImageMagick](http://php.net/manual/en/book.imagick.php)
  - See: [Installing/Configuring](http://php.net/manual/en/imagick.setup.php)

## Installation

- Unpack in the *modules* folder (currently in the root of your Drupal 8
installation) and enable in `/admin/modules`.

## Setting Up an Archive

- Install and enable module per instructions above.
- Navigate to `/admin/config/development/web-page-archive`.
- Click `Add Archive` button.
- Set options for your archive:
  - `Label`
    - The name of your archive
  - `XML Sitemap URL`
    - The url to your site's sitemap.xml file.
  - `Capture Screenshot?`
    - Check this box to get screenshots.
  - `Capture HTML?`
    - Check this box to get HTML snapshots.
- Click `Save` button.

## Running Manually

- Navigate to `/admin/config/development/web-page-archive`.
- In the far right hand column of the archive you wish to start click the
dropdown arrow, and click the `Start Run` button.
- Read the instructions and then click the `Start Run` button to start the
batch processing.
- Depending on the size of your site it may take a little while for this job to
run. You will be navigated to the snapshot overview page upon completion.

## Running Snapshot Capturing via Cron

- TODO: This isn't implemented yet.

## Viewing the Snapshots

- Navigate to `/admin/config/development/web-page-archive`.
- Click the `View Run History` button next the archive you wish to view in
more detail.
- TODO: This isn't implemented yet.

## Contributing

This is still an alpha release module, and features are continuously being
added. If you would like to assist in the development of this module, we
welcome your help.

Please follow the standards as explained in the Examples for Developers module:

http://cgit.drupalcode.org/examples/tree/STANDARDS.md

### Helpful Links

- [Drupal.org Repo](https://www.drupal.org/sandbox/pobster/2888559)
- [Drupal.org Issue Queue](https://www.drupal.org/project/issues/2888559)
- [GitHub.com Repo](https://github.com/WidgetsBurritos/web_page_archive)

### Ways You Can Help

- Use the Module and Let Us Know Your Thoughts
- Report Bugs
- Submit Ideas/Patches on drupal.org
- Submit Pull Requests on github.com
- Write Tests
- Write/Edit Documentation
