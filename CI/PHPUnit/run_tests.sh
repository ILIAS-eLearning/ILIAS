#!/bin/bash

./libs/composer/vendor/phpunit/phpunit/phpunit -c ./CI/PHPUnit/phpunit.xml $@
