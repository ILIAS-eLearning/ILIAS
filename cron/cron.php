<?php
chdir(dirname(__FILE__));
chdir('..');


include_once "./cron/classes/class.ilCronAuthentication.php";

$cron_auth =& new ilCronAuthentication();

if($_SERVER['argc'] != 4)
{
	die("Usage: cron.php username password client\n");
}
$cron_auth->setUsername($_SERVER['argv'][1]);
$cron_auth->setPassword($_SERVER['argv'][2]);
$cron_auth->setClient($_SERVER['argv'][3]);

if(!$cron_auth->authenticate())
{
	die($cron_auth->getMessage()."\n");
}

include_once './include/inc.header.php';

// Start checks here
include_once './cron/classes/class.ilCronCheck.php';

$cron_check =& new ilCronCheck();
$cron_check->start();

$cron_auth->logout();
?>