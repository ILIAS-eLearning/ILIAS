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
* questionpool script used to call the questionpool objects
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package assessment
*/
define("ILIAS_MODULE", "survey");
chdir("..");
require_once "./include/inc.header.php";
require_once "./survey/classes/class.ilObjSurveyQuestionPoolGUI.php";

// for security
//unset($id);

//determine call mode for object classes
//TODO: don't use same var $id for both
//if (isset($_GET["obj_id"]))
//{
//	$call_by_reference = false;
//	$id = $_GET["obj_id"];
//}
//else
//{
//	$call_by_reference = true;
//	$id = $_GET["ref_id"];
//}

// exit if no valid ID was given
//if (!isset($_GET["ref_id"]))
//{
//	$ilias->raiseError("No valid ID given! Action aborted", $this->ilias->error_obj->MESSAGE);
//}

//if (!isset($_GET["type"]))
//{
//	if ($call_by_reference)
//	{
//		$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
//	}
//	else
//	{
//		$obj = $ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
//	}

//	$_GET["type"] = $obj->getType();
//}

// determine command
//if (($cmd = $_GET["cmd"]) == "gateway")
//{
	// surpress warning if POST is not set
//	@$cmd = key($_POST["cmd"]);

//}

//if (empty($cmd)) // if no cmd is given default to first property
//{
//	$cmd = $_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
//}

//if ($_GET["cmd"] == "post")
//{
//	$cmd = key($_POST["cmd"]);
//	unset($_GET["cmd"]);
//}

// determine object type
//if ($_POST["new_type"] && (($cmd == "create") || ($cmd == "import")))
//{
//	$obj_type = $_POST["new_type"];
//}
//elseif ($_GET["new_type"])
//{
//	$obj_type = $_GET["new_type"];
//}
//else
//{
//	$obj_type = $_GET["type"];
//}

// call gui object method
//$method = $cmd."Object";
//$class_name = $objDefinition->getClassName($obj_type);
//$module = $objDefinition->getModule($obj_type);
//$module_dir = ($module == "")
//	? ""
//	: $module."/";

//$class_constr = "ilObj".$class_name."GUI";
//require_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
//echo $class_constr.":".$method;
// for security
unset($id);

//determine call mode for object classes
//TODO: don't use same var $id for both
if ($_GET["obj_id"] > 0)
{
	$call_by_reference = false;
	$id = $_GET["obj_id"];
}
else
{
	$call_by_reference = true;
	$id = $_GET["ref_id"];
}

// exit if no valid ID was given
if (!isset($_GET["ref_id"]))
{
	$ilias->raiseError("No valid ID given! Action aborted", $this->ilias->error_obj->MESSAGE);
}

//if ((strcmp($_GET["cmd"], "questions") == 0) and (($_POST["cmd"]["create"]) or ($_POST["id"]) or ($_GET["edit"]))) {
//{
//	$prepare_output = false;
//} else {
//	$prepare_output = true;
//}
//$obj = new $class_constr($data, $id, $call_by_reference, $prepare_output);
//$obj->$method();
//$tpl->show();

$ilCtrl->setTargetScript("questionpool.php");
$ilCtrl->getCallStructure("ilobjsurveyquestionpoolgui");
$qp_gui =& new ilObjSurveyQuestionPoolGUI("", $_GET["ref_id"], true, true);
$ilCtrl->forwardCommand($qp_gui);

$tpl->show();
?>


