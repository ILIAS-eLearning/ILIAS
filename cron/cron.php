<?php
chdir(dirname(__FILE__));
chdir('..');


include_once './Services/Cron/classes/class.ilCronStartUp.php';

if($_SERVER['argc'] < 4)
{
	die("Usage: cron.php username password client\n");
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

/*
include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_CRON);

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);


include_once './include/inc.header.php';

// Start checks here
include_once './cron/classes/class.ilCronCheck.php';
$cron_check = new ilCronCheck();
$cron_check->start();

include_once './Services/Cron/classes/class.ilCronManager.php';
ilCronManager::runActiveJobs();
*/
?>