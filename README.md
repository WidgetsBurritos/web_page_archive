# Web Page Archive

The Web Page Archive module allows you to use Drupal to perform periodic
snapshots on local and remote websites based on a specified XML sitemap.

This project is currently under active development and is still in early
alpha stages.

## Requirements

- Drupal 8.x
- PHP 5.6+
- PHP extensions: ext-openssl

## Installation

- This module must be installed using composer. To do so the following should be
included in your `composer.json` file:

```
"require": {
  "drupal/web_page_archive": "*"
},
"scripts": {
  "post-install-cmd": [
    "PhantomInstaller\\Installer::installPhantomJS"
  ]
}
```

- Then run `composer update`.

## Setting Up an Archive

- Install and enable module per instructions above.
- Navigate to `/admin/config/system/web-page-archive`.
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

- Navigate to `/admin/config/system/web-page-archive`.
- In the far right hand column of the archive you wish to start click the
dropdown arrow, and click the `Start Run` button.
- Read the instructions and then click the `Start Run` button to start the
batch processing.
- Depending on the size of your site it may take a little while for this job to
run. You will be navigated to the snapshot overview page upon completion.

## Running Snapshot Capturing via Cron

- TODO: This isn't implemented yet.

## Viewing the Snapshots

- Navigate to `/admin/config/system/web-page-archive`.
- Click the `View Run History` button next the archive you wish to view in
more detail.
- TODO: This isn't implemented yet.

## Uninstalling the Module

If you need to uninstall web page archive, you will need to remove the runs and field config. This can be done in one of two ways:

1. Navigate to `/admin/config/system/web-page-archive/uninstall` and click the button. Then proceed to uninstall the module.
2. Run `drush web-page-archive-prepare-uninstall` (full command) or `drush wpa-pu` (shorthand). Then proceed to uninstall the module.

## Contributing

This is still an alpha release module, and features are continuously being
added. If you would like to assist in the development of this module, we
welcome your help.

Please follow the standards as explained in the Examples for Developers module:

http://cgit.drupalcode.org/examples/tree/STANDARDS.md

### Helpful Links

- [Drupal.org Repo](https://www.drupal.org/project/web_page_archive)
- [Drupal.org Issue Queue](https://www.drupal.org/project/issues/2888559)
- [GitHub.com Repo](https://github.com/WidgetsBurritos/web_page_archive)
- [UML Diagrams](diagrams)

### Ways You Can Help

- Use the Module and Let Us Know Your Thoughts
- Report Bugs
- Submit Ideas/Patches on drupal.org
- Submit Pull Requests on github.com
- Write Tests
- Write/Edit Documentation
