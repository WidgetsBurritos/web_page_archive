#!/bin/bash

# Add an optional statement to see that this is running in Travis CI.
echo "running drupal_ti/before/before_script.sh"

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# The first time this is run, it will install Drupal.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Change to the Drupal directory
cd "$DRUPAL_TI_DRUPAL_DIR"

composer require "mtdowling/cron-expression:1.2.0"\
  "microweber/screen:1.0.*"\
  "t1gor/robots-txt-parser:0.2.3"\
  "kukulich/fshl:2.1.0"
