<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* registration confirmation script for ilias
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:
*/

// jump to setup if ILIAS is not installed
if(!file_exists(getcwd().'/ilias.ini.php'))
{
    header('Location: ./setup/setup.php');
	exit();
}

// start correct client
// if no client_id is given, default client is loaded (in class.ilias.php)
if (isset($_GET["client_id"]))
{
	$cookie_domain = $_SERVER['SERVER_NAME'];
	$cookie_path = dirname( $_SERVER['PHP_SELF'] );
	$cookie_path .= (!preg_match("/\/$/", $cookie_path)) ? "/" : "";
	
	$cookie_domain = ''; // Temporary Fix
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);
	
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once 'include/inc.header.php';

$ilCtrl->initBaseClass('ilStartUpGUI');
$ilCtrl->setCmd('confirmRegistration');
$ilCtrl->setTargetScript('ilias.php');
$ilCtrl->callBaseClass();

exit();
?>