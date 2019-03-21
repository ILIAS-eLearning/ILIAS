#!/bin/bash

libs/composer/vendor/bin/phpstan analyse -c phpstan.neon -l 3 -n --no-progress . $@;

libs/composer/vendor/phpunit/phpunit/phpunit --bootstrap ./libs/composer/vendor/autoload.php --configuration ./Services/PHPUnit/config/PhpUnitConfig.xml --exclude-group needsInstalledILIAS --verbose $@;
