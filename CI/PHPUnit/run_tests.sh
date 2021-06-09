#!/bin/bash

./libs/composer/vendor/phpunit/phpunit/phpunit tests/ILIASSuite.php --bootstrap ./libs/composer/vendor/autoload.php --exclude-group needsInstalledILIAS --verbose $@
