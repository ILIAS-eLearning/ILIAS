<?php
/**
* adm_object
* main script for administration console
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "include/inc.header.php";
require_once "classes/class.Object.php";	// base class for all Object Types
require_once "classes/class.ObjectOut.php";

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
	$ilias->raiseError("No valid ID given! Action aborted",$this->ilias->error_obj->MESSAGE);
}

if (!isset($_GET["type"]))
{
	if ($call_by_reference)
	{
		$obj = getObjectByReference($id);
	}
	else
	{
		$obj = getObject($id);
	}
	
	$_GET["type"] = $obj["type"];	
}

//if no cmd is given default to first property
if (!isset($_GET["cmd"]))
{
	$_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
}

// CREATE OBJECT CALLS 'createObject' METHOD OF THE NEW OBJECT
if($_REQUEST["new_type"])
{
	$obj_type = $_REQUEST["new_type"];
}
else
{
	$obj_type = $_GET["type"];
}

$method = $_GET["cmd"]."Object";
// build object instance
// e.g: cmd = 'view' type = 'frm'
// => $obj = new ForumObject(); $obj->viewObject()
$class_name = $objDefinition->getClassName($obj_type);
$class_constr = $class_name."Object";
require_once("./classes/class.".$class_name."Object.php");
$obj = new $class_constr($id,$call_by_reference);
// call object method
switch ($_GET["cmd"])
{
	// no more view() here! all calls moved to "out" class
	case "view":
		break;

	// no more save() here! all calls moved to "out" class
	case "save":
		break;

	// no more update() here! all calls moved to "out" class
	case "update":
		break;

	// edit object
	case "edit":
		$data = $obj->editObject($_GET["order"], $_GET["direction"]);
		break;

	// create object
	case "create":
		$data = $obj->createObject($id, $_POST["new_type"]);
		break;

	// show permission templates of object
	case "perm":
		$data = $obj->permObject();
		break;

	// save permission templates of object
	case "permSave":
		$data = $obj->permSaveObject($_POST["perm"], $_POST["stop_inherit"], $_GET["type"],
			$_POST["template_perm"], $_POST["recursive"]);
		break;

	// functions that shouldnt be called here
	case "delete":
	case "clone":
		echo "delete or clone called !!!!!!!!!!!!!!";
		// shouldn't be called here, just a test
		break;

	// no more gateway() here! all calls moved to "out" class
	case "gateway":
		break;

	default:
		$data = $obj->$method();
		break;
}

// CALL OUTPUT METHOD OF OBJECT
$class_constr = $class_name."ObjectOut";
require_once("./classes/class.".$class_name."ObjectOut.php");
//echo "$class_constr().$method<br>";
$obj = new $class_constr($data, $id, $call_by_reference);
$obj->readObject($class_name."Object");
$obj->prepareOutput();
$obj->$method();

// display basicdata formular
// TODO: must be changed for clientel processing
if ($_GET["cmd"] == "view" && $_GET["type"] == "adm")
{
	$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
	$tpl->setCurrentBlock("systemsettings");
	require_once "./include/inc.basicdata.php";
	$tpl->parseCurrentBlock();
}
$tpl->show();
?>
