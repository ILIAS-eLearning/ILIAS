#!/bin/bash

./vendor/composer/vendor/phpunit/phpunit/phpunit -c ./scripts/PHPUnit/phpunit.xml "$@"
