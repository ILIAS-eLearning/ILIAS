<?php
chdir(dirname(__FILE__));
chdir('..');

include_once './Services/Cron/classes/class.ilCronStartUp.php';

if ($_SERVER['argc'] < 4) {
    echo "Usage: cron.php username password client\n";
    exit(1);
}

$client = $_SERVER['argv'][3];
$login = $_SERVER['argv'][1];
$password = $_SERVER['argv'][2];

$cron = new ilCronStartUp(
    $client,
    $login,
    $password
);

try {
    $cron->authenticate();

    $cronManager = new ilStrictCliCronManager(
        new ilCronManager($DIC->settings(), $DIC->logger()->root())
    );
    $cronManager->runActiveJobs();

    $cron->logout();
} catch (Exception $e) {
    $cron->logout();

    echo $e->getMessage() . "\n";
    exit(1);
}
