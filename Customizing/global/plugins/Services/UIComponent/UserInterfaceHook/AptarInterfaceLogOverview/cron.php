<?php
chdir(dirname(__FILE__));
$ilias_main_directory = './';
while(!file_exists($ilias_main_directory . 'ilias.ini.php'))
{
	$ilias_main_directory .= '../';
}
chdir($ilias_main_directory);

include_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_CRON);

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE['ilClientId'] = $_SERVER['argv'][3];
$_POST['username']     = $_SERVER['argv'][1];
$_POST['password']     = $_SERVER['argv'][2];

if($_SERVER['argc'] < 4)
{
	die('Usage: cron.php username password client\n');
}

require_once 'include/inc.header.php';

require_once dirname(__FILE__) . '/classes/class.ilAptarInterfaceLogOverviewPlugin.php';
ilAptarInterfaceLogOverviewPlugin::getInstance()->run();