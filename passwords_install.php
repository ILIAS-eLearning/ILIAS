<?php
global $ilDB;

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_CRON);

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE["ilClientId"] = 'default';
$_POST['username']     = 'root';
$_POST['password']     = 'homer';

include_once './include/inc.header.php';

/***************************** Types ****************************/

// Only for < 5.2.x

// Generate Hash and put it to client.ini.php
require_once 'Services/Migration/DBUpdate_Password/classes/class.ilPasswordUtils.php';

$salt_location = CLIENT_DATA_DIR . '/pwsalt.txt';
if(!is_file($salt_location))
{
	$result = @file_put_contents(
		$salt_location,
		substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(16))), 0, 22)
	);
	if(!$result)
	{
		throw new ilException("Could not create the client salt for bcrypt password hashing. Please contact an administrator.");
	}
}

if(!is_file($salt_location) || !is_readable($salt_location))
{
	throw new ilException("Could not determine the client salt for bcrypt password hashing. Please contact an administrator.");
}


