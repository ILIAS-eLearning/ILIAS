#!/bin/bash
TMP_DIR="/tmp"

PHP_CS_FIXER_RESULTS_PATH="$TMP_DIR/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"

PHP_VERSION=$(phpenv version-name)
PHPUNIT_PATH="$TMP_DIR/phpunit_latest_$PHP_VERSION.csv"
PHPUNIT_PATH_TMP="$TMP_DIR/phpunit_changed_$PHP_VERSION.csv"
PHPUNIT_RESULTS_PATH="$TMP_DIR/phpunit_results_$PHP_VERSION"

DICTO_PATH="$TMP_DIR/dicto_latest.csv"

TRAVIS_RESULTS_DIRECTORY="$TMP_DIR/CI-Results/"

DATE=`date '+%Y-%m-%d %H:%M:%S'`
UNIXDATE=`date '+%s'`

PRE="\t*** "
