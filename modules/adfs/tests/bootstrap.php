<?php

$projectRoot = dirname(__DIR__);
/** @psalm-suppress UnresolvableInclude */
require_once($projectRoot.'/vendor/autoload.php');

// Symlink module into ssp vendor lib so that templates and urls can resolve correctly
$linkPath = $projectRoot.'/vendor/simplesamlphp/simplesamlphp/modules/adfs';
if (file_exists($linkPath) === false) {
    echo "Linking '$linkPath' to '$projectRoot'\n";
    symlink($projectRoot, $linkPath);
}
