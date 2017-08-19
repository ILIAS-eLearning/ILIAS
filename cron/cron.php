<?php
chdir(dirname(__FILE__));
chdir('..');


include_once './Services/Cron/classes/class.ilCronStartUp.php';

if($_SERVER['argc'] < 4)
{
	echo "Usage: cron.php username password client\n";
	exit(1);
}

$cron = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);

try {
	$cron->initIlias();
	$cron->authenticate();

	include_once './Services/Cron/classes/class.ilCronManager.php';
	ilCronManager::runActiveJobs();

	$cron->logout();
}
catch(Exception $e)
{
	$cron->logout();

	echo $e->getMessage()."\n";
	exit(1);
}