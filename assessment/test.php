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
* assessment test script used to call the test objects
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package assessment
*/
/*define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";

// for security
unset($id);

//determine call mode for object classes
//TODO: don't use same var $id for both
if (isset($_GET["obj_id"]))
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

// PAYMENT STUFF
// check if object is purchased
include_once './payment/classes/class.ilPaymentObject.php';
include_once './classes/class.ilSearch.php';

if(!ilPaymentObject::_hasAccess($_GET['ref_id']))
{
	ilUtil::redirect('../payment/start_purchase.php?ref_id='.$_GET['ref_id']);
}
if(!ilSearch::_checkParentConditions($_GET['ref_id']))
{
	$ilias->error_obj->raiseError($lng->txt('access_denied'),$ilias->error_obj->WARNING);
}



if (!isset($_GET["type"]))
{
	if ($call_by_reference)
	{
		$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
	}
	else
	{
		$obj = $ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
	}

	$_GET["type"] = $obj->getType();
}

// determine command
if (($cmd = $_GET["cmd"]) == "gateway")
{
	// surpress warning if POST is not set
	@$cmd = key($_POST["cmd"]);

}

if (empty($cmd)) // if no cmd is given default to first property
{
	$cmd = $_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
}

if ($_GET["cmd"] == "post")
{
	$cmd = key($_POST["cmd"]);
	unset($_GET["cmd"]);
}

// determine object type
if ($_POST["new_type"] && (($cmd == "create") || ($cmd == "import")))
{
	$obj_type = $_POST["new_type"];
}
elseif ($_GET["new_type"])
{
	$obj_type = $_GET["new_type"];
}
else
{
	$obj_type = $_GET["type"];
}
$chapter_id = $_GET["chapter_id"];
// call gui object method
$method = $cmd."Object";
$class_name = $objDefinition->getClassName($obj_type);
$module = $objDefinition->getModule($obj_type);
$module_dir = ($module == "")
	? ""
	: $module."/";

$class_constr = "ilObj".$class_name."GUI";
require_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
//arlon added
if($chapter_id!="")
{
	require_once("./assessment/classes/class.ilObjTestGUI.php");
	switch ($_GET["cmd"]) 
	{
		case "run":
		case "eval_a":
		case "eval_stat":
			$prepare_output = false;
			break;
		default:
			$prepare_output = true;
			break;
	}
	//arlon modified
	$obj = new ilObjTestGUI($data,$id,$call_by_reference,$prepare_output,$chapter_id);
	//if($_GET["sequence"]!="")
	//{
	//	$obj->questionbrowser();
	//}
	//else
	//{
		$obj->runObject();
	//}
}
else
{
	//Arlon added
	if(DEVMODE)
	{
	}
	require_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
	//echo $class_constr.":".$method;
	switch ($_GET["cmd"]) 
	{
		case "run":
		case "eval_a":
		case "eval_stat":
			$prepare_output = false;
			break;
		default:
			$prepare_output = true;
			break;
	}
	$obj = new $class_constr($data, $id, $call_by_reference, $prepare_output,$chapter_id);
	$obj->$method();
}
$tpl->show();
?>
*/
define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";
require_once "./assessment/classes/class.ilObjTestGUI.php";

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

switch ($_GET["cmd"]) 
{
	case "run":
		$prepare_output = false;
		break;
	default:
		$prepare_output = true;
		break;
}
$ilCtrl->setTargetScript("test.php");
$ilCtrl->getCallStructure("ilobjtestgui");
$tst_gui =& new ilObjTestGUI("", $_GET["ref_id"], true, $prepare_output);
$ilCtrl->forwardCommand($tst_gui);

$tpl->show();
?>
