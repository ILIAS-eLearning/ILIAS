#!/bin/bash

./vendor/composer/vendor/bin/phpstan analyse -c ./CI/PHPStan/phpstan.neon "$@"
