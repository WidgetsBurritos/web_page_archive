<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);



// my custom autoloader
function custom_wpa_autoloader($class) {
  $class = str_replace('Drupal\\web_page_archive', '', $class);
  $class = str_replace('\\', '/', $class);
  include '/app/src/'.$class.'.php';
}

// register the autoloader
spl_autoload_register('custom_wpa_autoloader');


// TODO: Prevent direct execution through drupal?
require_once '/app/vendor/autoload.php';

use Drupal\web_page_archive\Component\HeadlessChromeCapture;

// Docker's node/npm paths:
$node = '/usr/local/bin/node';
$npm = '/usr/local/bin/npm';
$node_modules = '/usr/local/lib/node_modules/';
$hcc = new HeadlessChromeCapture($node, $npm, $node_modules);
$ret = $hcc->capture('https://www.rackspace.com', 'png');
print_r($ret);
