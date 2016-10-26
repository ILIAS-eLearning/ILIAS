<?php
chdir(dirname(__FILE__));
chdir('..');


include_once './Services/Cron/classes/class.ilCronStartUp.php';

if($_SERVER['argc'] < 4)
{
	echo "Usage: cron.php username password client\n";
	exit(1);
}

try {
	$cron = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);
	$cron->initIlias();
	$cron->authenticate();
	
	
	include_once './Services/Cron/classes/class.ilCronManager.php';
	ilCronManager::runActiveJobs();
	
}
catch(Exception $e)
{
	echo $e->getMessage()."\n";
	exit(1);
}
?>