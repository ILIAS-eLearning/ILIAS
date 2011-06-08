<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Personal Workspace
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*/

global $ilCtrl, $tpl;

$_GET["baseClass"] = "ilPersonalDesktopGUI";

require_once "include/inc.header.php";
include_once "./Services/PersonalDesktop/classes/class.ilPersonalDesktopGUI.php";

$ilCtrl->setTargetScript("ilias.php");

$ilCtrl->getCallStructure("ilpersonaldesktopgui");

$wsp_gui = new ilPersonalDesktopGUI();

$ilCtrl->setParameterByClass("ilpersonalworkspacegui", "wsp_id", $_REQUEST["wsp_id"]);
$ilCtrl->setCmd("jumptoworkspace");

$ilCtrl->forwardCommand($wsp_gui);

$tpl->show();

?>