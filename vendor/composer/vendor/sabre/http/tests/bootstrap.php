<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

ini_set('error_reporting', (string) (E_ALL | E_STRICT | E_DEPRECATED));

// Composer autoloader
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    include __DIR__.'/../vendor/autoload.php';
} else {
    // We may be running as a dependency inside some other repo.
    // So we are probably in vendor/sabre/http/tests of that repo.
    // Go up 3 levels to find autoload.php
    include __DIR__.'/../../../autoload.php';
}
