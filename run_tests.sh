#!/bin/bash

cp Services/PHPUnit/config/cfg.phpunit.template.php Services/PHPUnit/config/cfg.phpunit.php
libs/composer/vendor/phpunit/phpunit/phpunit --bootstrap ./libs/composer/vendor/autoload.php --configuration ./Services/PHPUnit/config/PhpUnitConfig.xml --exclude-group needsInstalledILIAS $@;
