<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir(dirname(__FILE__));

$ilias_main_directory = './';
$cookie_path          = dirname($_SERVER['PHP_SELF']);

$i = 0;
while(!file_exists($ilias_main_directory . 'ilias.ini.php') && $i < 20)
{
	$ilias_main_directory .= '../';
	++$i;

	$cookie_path = dirname($cookie_path);
}
chdir($ilias_main_directory);

if(!file_exists(getcwd() . '/ilias.ini.php'))
{
	die('Please ensure ILIAS is installed!');
}

$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

if(isset($_GET["client_id"]))
{
	if($cookie_path == "\\")
	{
		$cookie_path = '/';
	}

	setcookie('ilClientId', $_GET['client_id'], 0, $cookie_path, '');
	$_COOKIE['ilClientId'] = $_GET['client_id'];
}
define('IL_COOKIE_PATH', $cookie_path);


require_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SAML);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

require_once 'Services/Saml/classes/class.ilSamlAuthFactory.php';
$factory = new ilSamlAuthFactory();
$auth = $factory->auth();

require_once 'libs/composer/vendor/simplesamlphp/simplesamlphp/modules/saml/www/sp/saml1-acs.php';
