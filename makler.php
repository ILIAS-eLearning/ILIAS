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
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/User/classes/class.ilObjUser.php");

ilInitialisation::initILIAS();
$GLOBALS["ilUser"] = new ilObjUser(gevSettings::getInstance()->getAgentOfferUserId());


if (!isset($_GET["baseClass"])) {
	$ilCtrl->initBaseClass("gevAgentOfferGUI");
}
$ilCtrl->setTargetScript("makler.php");

$ilCtrl->callBaseClass();
$ilBench->save();


?>