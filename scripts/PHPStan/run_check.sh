#!/bin/bash

./libs/composer/vendor/bin/phpstan analyse -c ./scripts/PHPStan/phpstan.neon "$@"
