#!/bin/bash
TMP_DIR="/tmp"

PHP_CS_FIXER_RESULTS_PATH="$TMP_DIR/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"

PHPUNIT_PATH="$TMP_DIR/phpunit_latest.csv"
PHPUNIT_PATH_TMP="$TMP_DIR/phpunit_changed.csv"
PHPUNIT_RESULTS_PATH="$TMP_DIR/phpunit_results"

DICTO_PATH="$TMP_DIR/dicto_latest.csv"

TRAVIS_RESULTS_DIRECTORY="$TMP_DIR/CI-Results/"

DATE=`date '+%Y-%m-%d %H:%M:%S'`
UNIXDATE=`date '+%s'`

PRE="\t*** "