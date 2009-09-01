<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* ilias.php. main script.
*
* If you want to use this script your base class must be declared
* within modules.xml.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/

require_once "./include/inc.header.php";

/*function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $ilCtrl;
echo "<br>".$errstr;
	switch ($errno)
	{
    case E_USER_ERROR:
	case E_USER_WARNING:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;
	}
	return false;
}
$old_error_handler = set_error_handler("myErrorHandler");*/

global $ilCtrl, $ilBench, $ilLog;

$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();
?>
