#!/bin/bash

./libs/composer/vendor/bin/phpstan analyse -c ./CI/PHPStan/phpstan.neon "$@"
