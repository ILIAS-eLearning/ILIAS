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
	$ilCtrl->initBaseClass("gevLoginDataGUI");
}
$ilCtrl->setTargetScript("gev_logindata.php");
if (!$ilCtrl->getCmd()) {
	$ilCtrl->setCmd("showLoginHelper");
}

$ilCtrl->callBaseClass();
$ilBench->save();
?>