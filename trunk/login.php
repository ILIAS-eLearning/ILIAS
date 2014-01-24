<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* login script for ilias
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-layout
*/


// jump to setup if ILIAS3 is not installed
if (!file_exists(getcwd()."/ilias.ini.php"))
{
    header("Location: ./setup/setup.php");
	exit();
}

// start correct client
// if no client_id is given, default client is loaded (in class.ilias.php)
if (isset($_GET["client_id"]))
{
	$cookie_domain = $_SERVER['SERVER_NAME'];
	$cookie_path = dirname( $_SERVER['PHP_SELF'] );

	/* if ilias is called directly within the docroot $cookie_path
	is set to '/' expecting on servers running under windows..
	here it is set to '\'.
	in both cases a further '/' won't be appended due to the following regex
	*/
	$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";
	
	if($cookie_path == "\\") $cookie_path = '/';
	
	$cookie_domain = ''; // Temporary Fix
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);
	
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd("showLogin");
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();

exit;
?>
