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

if (isset($_GET["goto"]) && ($_GET["goto"] == "startNARegistration" || $_GET["goto"] == "startAgentRegistration")) {
	$ilCtrl->setCmd($_GET["goto"]);
}
else {
	if(isset($_GET["cmdClass"]) && $_GET["cmdClass"] == "ilpasswordassistancegui") {
		$cmd = $_GET["cmd"];
		$ilCtrl->setCmd($cmd);
	}else {
		if (isset($_POST["cmd"])) {
			$cmds = array_keys($_POST["cmd"]);
			$ilCtrl->setCmd($cmds[0]);
		} else if (isset($_GET["cmd"])) {
				$ilCtrl->setCmd($_GET["cmd"]);
		}
		else {

			$ilCtrl->setCmd("startRegistration");
		}
	}
}

$ilCtrl->callBaseClass();
$ilBench->save();
?>