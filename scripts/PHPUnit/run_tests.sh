#!/bin/bash

./vendor/composer/vendor/phpunit/phpunit/phpunit -c ./CI/PHPUnit/phpunit.xml "$@"
