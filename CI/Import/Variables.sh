#!/bin/bash

# Setup the results data directories
RESULTS_DATA_DIRECTORY="CI/results/data"

RESULTS_DATA_DIRECTORY_CSFIXER="$RESULTS_DATA_DIRECTORY/phpfix_results"

PHP_VERSION=$(phpenv version-name)
RESULTS_DATA_DIRECTORY_PHPUNIT="$RESULTS_DATA_DIRECTORY/phpunit_latest_$PHP_VERSION.csv"
RESULTS_DATA_DIRECTORY_PHPUNIT_RESULTS_PATH="/tmp/phpunit_results_$PHP_VERSION"
RESULTS_DATA_DIRECTORY_PHPUNIT_TMP="$RESULTS_DATA_DIRECTORY/phpunit_changed_$PHP_VERSION.csv"

RESULTS_DATA_DIRECTORY_DICTO="$RESULTS_DATA_DIRECTORY/dicto_latest.csv"

# CS fixer (Todo for later)
PHP_CS_FIXER_RESULTS_PATH="$RESULTS_DATA_DIRECTORY/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"

TRAVIS_RESULTS_DIRECTORY="/tmp/CI-Results"

# Why???
DATE=`date '+%Y-%m-%d %H:%M:%S'`
UNIXDATE=`date '+%s'`

# Why??
PRE="\t*** "
