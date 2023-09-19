#!/bin/bash

./vendor/composer/vendor/bin/phpstan analyse -c ./scripts/PHPStan/phpstan.neon "$@"
