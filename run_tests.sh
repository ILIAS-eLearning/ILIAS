#!/bin/sh

phpunit --bootstrap libs/composer/vendor/autoload.php $@ CaT/test
