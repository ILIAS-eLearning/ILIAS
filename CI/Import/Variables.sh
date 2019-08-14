#!/bin/bash
PHP_CS_FIXER_RESULTS_PATH="/tmp/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"

PHPSTAN="libs/composer/vendor/bin/phpstan"

PHPUNIT_PATH="/tmp/phpunit_latest.csv"
PHPUNIT_PATH_TMP="/tmp/phpunit_changed.csv"
PHPUNIT_RESULTS_PATH="/tmp/phpunit_results"

DICTO_PATH="/tmp/dicto_latest.csv"

TRAVIS_RESULTS_DIRECTORY="/tmp/CI-Results/"

DATE=`date '+%Y-%m-%d %H:%M:%S'`
UNIXDATE=`date '+%s'`

PRE="\t*** "