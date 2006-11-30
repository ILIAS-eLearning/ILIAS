<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* edit scorm modules
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");

require_once "./include/inc.header.php";
require_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";

$lng->loadLanguageModule("content");

// check write permission
if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$ref_id=$_GET["ref_id"];
$obj_id = ilObject::_lookupObjectId($ref_id);
$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

switch ($type)
{
	case "scorm":
		//SCORM
		require_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php";
		$ilCtrl->setTargetScript("sahs_edit.php");

		$ilCtrl->getCallStructure("ilobjscormlearningmodulegui");
		$scorm_gui =& new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"],true, false);
		//$scorm_gui->executeCommand();
		$ilCtrl->forwardCommand($scorm_gui);
		break;

	case "aicc":
		//AICC
		require_once "./Modules/ScormAicc/classes/class.ilObjAICCLearningModuleGUI.php";
		$ilCtrl->setTargetScript("sahs_edit.php");

		$ilCtrl->getCallStructure("ilobjaicclearningmodulegui");
		$aicc_gui =& new ilObjAICCLearningModuleGUI("", $_GET["ref_id"],true, false);
		//$aicc_gui->executeCommand();
		$ilCtrl->forwardCommand($aicc_gui);
		break;

	case "hacp":
		//HACP
		require_once "./Modules/ScormAicc/classes/class.ilObjHACPLearningModuleGUI.php";
		$ilCtrl->setTargetScript("sahs_edit.php");

		$ilCtrl->getCallStructure("ilobjhacplearningmodulegui");
		$hacp_gui =& new ilObjHACPLearningModuleGUI("", $_GET["ref_id"],true, false);
		//$hacp_gui->executeCommand();
		$ilCtrl->forwardCommand($hacp_gui);
		break;

	default:
		//unknown type
		$ilias->raiseError($lng->txt("unknown type in sahs_edit"),$ilias->error_obj->MESSAGE);
}
$tpl->show();
?>
