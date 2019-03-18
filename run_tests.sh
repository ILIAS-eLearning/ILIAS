#!/bin/bash

libs/composer/vendor/bin/phpstan analyse --configuration phpstan.neon --level 3 ./src $@;

libs/composer/vendor/phpunit/phpunit/phpunit --bootstrap ./libs/composer/vendor/autoload.php --configuration ./Services/PHPUnit/config/PhpUnitConfig.xml --exclude-group needsInstalledILIAS --verbose $@;
