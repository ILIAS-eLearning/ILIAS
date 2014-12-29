<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* registration form for new users
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id: register.php 33447 2012-03-01 14:00:01Z jluetzen $
*
* @package ilias-core
*/

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if (!isset($_GET["baseClass"])) {
	$ilCtrl->initBaseClass("gevRegistrationGUI");
}
$ilCtrl->setTargetScript("gev_registration.php");

if (!isset($_POST["cmd"])) {
	$ilCtrl->setCmd("startRegistration");
}
else {
	$cmds = array_keys($_POST["cmd"]);
	$ilCtrl->setCmd($cmds[0]);
}

$ilCtrl->callBaseClass();
$ilBench->save();


?>