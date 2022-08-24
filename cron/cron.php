<?php

declare(strict_types=1);

chdir(__DIR__);
chdir('..');

require_once './Services/Cron/classes/class.ilCronStartUp.php';

if ($_SERVER['argc'] < 4) {
    echo "Usage: cron.php username password client\n";
    exit(1);
}

[$script, $login, $password, $client] = $_SERVER['argv'];

$cron = new ilCronStartUp(
    $client,
    $login,
    $password
);

try {
    global $DIC;

    $cron->authenticate();

    $strictCronManager = new ilStrictCliCronManager(
        $DIC->cron()->manager()
    );
    $strictCronManager->runActiveJobs($DIC->user());

    $cron->logout();
} catch (Exception $e) {
    $cron->logout();

    echo $e->getMessage() . "\n";

    if (defined('DEVMODE') && DEVMODE) {
        echo $e->getTraceAsString() . "\n";
    }

    exit(1);
}
