<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

// jump to setup if ILIAS is not installed
if(!file_exists(getcwd() . '/ilias.ini.php'))
{
	header('Location: ./setup/setup.php');
	exit();
}

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SESSION_REMINDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

include_once 'Services/Authentication/classes/class.ilSessionReminderCheck.php';
$session_reminder_check = new ilSessionReminderCheck();
echo $session_reminder_check->getJsonResponse(
	ilUtil::stripSlashes($_GET['session_id']),
	(int)ilUtil::stripSlashes($_GET['countdown'])
);
exit();