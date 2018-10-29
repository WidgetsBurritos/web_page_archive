# Web Page Archive

The Web Page Archive module allows you to use Drupal to perform periodic snapshots and visual regression testing on local and remote websites based of a list of URLs or XML sitemaps, all within the familiar Drupal admin interface.

Watch [Texas Camp 2018 - Archiving and Visual Regression using Drupal 8](https://www.youtube.com/watch?v=G_gEr8V1Tjo) for a basic walkthrough of functionality and a demonstration.

See [Project Roadmap](https://www.drupal.org/node/2916976) to see what's on the horizon.

## Capture Utilities (Archiving)

Snapshots are performed by Capture Utility plugins. Web Page Archive provides the following capture utilities:

| Plugin | Machine Name | Purpose |
|-----------------|------------------------|-----------------------------------------------------------------------|
| HTML Capture Utility | wpa_html_capture | Captures raw HTML from URLs. |
| Screenshot Capture Utility | wpa_screenshot_capture | Capture Screenshots of URLs (uses [Headless Chrome](https://developers.google.com/web/updates/2017/04/headless-chrome#screenshots) or [PhantomJS](http://phantomjs.org/)). |
| Skeleton Capture Utility | wpa_skeleton_capture | Example code that provides a template for building additional capture utility plugins. |

## Comparison Utilities (Visual Regression)

Comparisons are performed by Comparison Utility plugins. Web Page Archive provides the following capture utilities:

| Plugin | Machine Name | Purpose | Applies to |
|-----------------|------------------------|-----------------------------------------------------------------------|---------|
| File: Size | web_page_archive_file_size_compare | Compares files based on size | Screenshots, HTML |
| HTML: Diff | wpa_html_diff_compare | Compares HTML line-by-line | HTML |
| Screenshot: Pixel | wpa_screenshot_capture_pixel_compare | Compares images and generates diff images (uses ImageMagick 7.0+) | Screenshots |
| Screenshot: Slider | wpa_screenshot_capture_slider_compare | Compares images via slider | Screenshots |

## Other modules extending Web Page Archive:

- [Performance Budget (Experimental)](https://www.drupal.org/project/performance_budget) - Creates and manages performance budgets for websites.
- [Configuration Archive (Experimental)](https://www.drupal.org/project/configuration_archive) - Creates and maintains snapshots of system configurations over time.

## Requirements
- Drupal 8.3+
- PHP 7.0+
- PHP extensions: `ext-openssl`
- Each capture utility may have additional requirements. See the respective installation guide for more information.
- A lot of storage space (especially when using screenshot capture utility)

## Installation

See the following guides for installing web page archive and capture utility dependencies:

- [Getting Started with Web Page Archive](https://www.drupal.org/docs/8/modules/web-page-archive/getting-started-with-the-web-page-archive-module)
- [Installing Headless Chrome](https://www.drupal.org/docs/8/modules/web-page-archive/installing-headless-chrome-or-chromium)
- [Installing PhantomJS](https://www.drupal.org/docs/8/modules/web-page-archive/installing-phantomjs)
- [Installing ImageMagick](https://www.drupal.org/docs/8/modules/web-page-archive/getting-started-with-web-page-archive/installing-imagemagick)
- [Comparing HTML and Screenshots](https://www.drupal.org/docs/8/modules/web-page-archive/getting-started-with-web-page-archive/comparing-html-and-screenshots)
- [Uninstalling Web Page Archive](https://www.drupal.org/docs/8/modules/web-page-archive/uninstalling-web-page-archive)

## Contributing

This is a relatively new module and features are continuously being added. If you would like to assist in the development of this module, we welcome your help.

Please follow the [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards).

### Ways You Can Help

- Use the Module and Let Us Know Your Thoughts
- Report Bugs
- Submit Ideas/Patches on drupal.org
- Submit Pull Requests on github.com
- Write Tests
- Write/Edit Documentation

### Helpful Links

- [Official Drupal.org Project Page](https://www.drupal.org/project/web_page_archive)
- [Official Web Page Archive Documentation](https://www.drupal.org/docs/8/modules/web-page-archive)
- [Drupal.org Issue Queue](https://www.drupal.org/project/issues/2888559)
- [GitHub.com Repo](https://github.com/WidgetsBurritos/web_page_archive)

### Maintainers

- David Stinemetze (aka @WidgetsBurritos) - [Drupal](https://www.drupal.org/u/widgetsburritos) / [GitHub](https://github.com/WidgetsBurritos)
- David Porter (aka @bighappyface) - [Drupal](https://www.drupal.org/u/bighappyface) / [GitHub](https://github.com/bighappyface)
- Paul Maddern (aka @pobster) - [Drupal](https://www.drupal.org/u/pobster) / [GitHub](https://github.com/pobtastic)
- Adrianna Flores (aka @vessel_adrift)
[Drupal](https://www.drupal.org/u/vessel_adrift) / [GitHub](https://github.com/xinbin)

This project has been sponsored by [Rackspace](https://www.rackspace.com).
