<?php declare(strict_types=1);

chdir(__DIR__);
chdir('..');

require_once './Services/Cron/classes/class.ilCronStartUp.php';

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
    exit(1);
}
