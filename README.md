# Web Page Archive

The Web Page Archive module allows you to use Drupal to perform periodic snapshots on local and remote websites based on a list of URLs or XML sitemaps.

This project is currently under active development and is still in early alpha stages.

Follow the development process here: [MVP release plan](https://www.drupal.org/node/2894031)

## Capture Utilities

Snapshots are performed by *Capture Utility* plugins. Web Page Archive provides the following capture utilities:

| Plugin | Machine Name | Purpose | Module |
|-----------------|------------------------|-----------------------------------------------------------------------|-----------------------------------|
| HTML Capture Utility | wpa_html_capture | Captures raw HTML from URLs. | **Module**: web_page_archive |
| Screenshot Capture Utility | wpa_screenshot_capture | Capture Screenshots of URLs (uses [PhantomJS](http://phantomjs.org/)). | **Submodule:** wpa_screenshot_capture |
| Skeleton Capture Utility | wpa_skeleton_capture | Example code that provides a template for building additional capture utility plugins. | **Submodule:** wpa_skeleton_capture |

## Requirements

- Drupal 8.3+
- PHP 7.0+
- PHP extensions: `ext-openssl`
- A lot of storage space (especially when using screenshot capture utility)

## Installation

To install `web_page_archive` module you must [manage your Drupal Dependencies with composer](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies).

You can install the module using the following the command:

```
composer require "drupal/web_page_archive"
```

Then enable `web_page_archive` module via usual methods.

If not running as UID 1, make sure you assign the `Administer Web Page Archive` permission to any necessary roles.

### Installing the Screenshot Capture Utility Submodule

The `wpa_screenshot_capture` submodule must also be installed via composer:

```
composer require "drupal/wpa_screenshot_capture"
```

Then you must modify the `scripts` property in the root composer JSON object to install PhantomJS via the `post-install-cmd` and/or `post-update-cmd` events:

```
"scripts": {
  "post-install-cmd": [
    "PhantomInstaller\\Installer::installPhantomJS"
  ],
  "post-update-cmd": [
    "PhantomInstaller\\Installer::installPhantomJS"
  ]
}
```

- Then run `composer update` to install PhantomJS.
- Finally enable `wpa_screenshot_capture` module via usual methods.

## Setting Up an Archive

- Install and enable module per instructions above.
- Navigate to `/admin/config/system/web-page-archive`.
- Click `Add Archive` button.
- Set options for your archive:
  - `Label`
    - The name of your archive
  - `Crontab schedule (relative to PHP's default timezone)`
    - Crontab-based schedule for running capture.
  - `Timeout (ms)`
    - Number of milliseconds to wait between captures (to avoid bombarding servers with connection requests).
  - `URL Type`
    - `None` - Desired capture utility doesn't require URLs for capturing.
    - `URL` - Capture specified URLs.
    - `Sitemap URL` - Capture all URLs references in an XML sitemap.
  - `URLs to Capture`
    - A list of URL(s) you wish to capture from if `URL` or `Sitemap URL` URL type was selected above.
- Click `Create new archive` button.
- Use the dropdown under the `Capture Utility` section to specify which capture utility you want to use.
- Click `Add` button.
- Fill out capture utility settings.
- Click `Add capture utility`.
- You can add additional capture utilities if you choose, but it is recommended to create separate config entities in these cases.

## Running Manually

- Navigate to `/admin/config/system/web-page-archive`.
- In the far right hand column of the archive you wish to start click the dropdown arrow, and click the `Start Run` button.
- Read the instructions and then click the `Start Run` button to start the batch processing.
- Depending on the size of your site and the capture utilities selected, it may take a little while for this job to run. You will be navigated to the snapshot overview page upon completion.

## Running Snapshot Capturing via Cron

If you wish to automatically run captures via cron, see [Configuring cron jobs using the cron command](https://www.drupal.org/docs/7/setting-up-cron-for-drupal/configuring-cron-jobs-using-the-cron-command) for more information on how to setup cron for your site.

To provide the most accurate timing it is recommended to set your system cron settings to run on `* * * * *`, but the module will cooperate with less frequent schedules as well.

Then just ensure your individual web page archive entities are configured with a proper crontab.

## Viewing the Snapshots

- Navigate to `/admin/config/system/web-page-archive`.
- Click the `View Run History` button next the archive you wish to view in more detail.
- You can sort and filter runs by various criteria.
- Click the `View Details` button next to the particular run you wish to look at.
- You can sort and filter captures by various criteria.
- Each capture utility will have its own way of rendering results.

## Uninstalling the Module

To uninstall web page archive, you will need to remove the runs and field config first. This can be done in one of two ways:

1. Navigate to `/admin/config/system/web-page-archive/uninstall` and click the `Delete web_page_archive data` button. Then proceed to uninstall the module via usual methods.
2. Run `drush web-page-archive-prepare-uninstall` (full command) or `drush wpa-pu` (shorthand). Then proceed to uninstall the module and submodules via usual methods.

## Contributing

This is still an alpha release module, and features are continuously being added. If you would like to assist in the development of this module, we welcome your help.

Please follow the standards as explained in the Examples for Developers module:

http://cgit.drupalcode.org/examples/tree/STANDARDS.md

### Ways You Can Help

- Use the Module and Let Us Know Your Thoughts
- Report Bugs
- Submit Ideas/Patches on drupal.org
- Submit Pull Requests on github.com
- Write Tests
- Write/Edit Documentation

### Helpful Links

- [Drupal.org Repo](https://www.drupal.org/project/web_page_archive)
- [Drupal.org Issue Queue](https://www.drupal.org/project/issues/2888559)
- [GitHub.com Repo](https://github.com/WidgetsBurritos/web_page_archive)
- [UML Diagrams](diagrams)
