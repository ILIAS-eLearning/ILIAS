<?php
/**
* adm_object
* main script for administration console
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "include/inc.header.php";

/*
echo "<pre>";
var_dump($_REQUEST);
echo "</pre>";
*/

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
		//$obj = getObjectByReference($id);
		$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
	}
	else
	{
		//$obj = getObject($id);
		$obj = $ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
	}
	
	$_GET["type"] = $obj->getType();	
}

//if no cmd is given default to first property
if (!isset($_GET["cmd"]))
{
	$_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
}

// CREATE OBJECT CALLS 'createObject' METHOD OF THE NEW OBJECT
if ($_POST["new_type"])
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

if ($_GET["cmd"] == "gateway")
{
	$cmd = key($_POST["cmd"]);
}
else
{
	$cmd = $_GET["cmd"];
}

$method = $cmd."Object";
// build object instance
// e.g: cmd = 'view' type = 'frm'
// => $obj = new ForumObject(); $obj->viewObject()
$class_name = $objDefinition->getClassName($obj_type);
//$class_constr = "ilObj".$class_name;
//require_once("./classes/class.ilObj".$class_name.".php");
//$obj = new $class_constr($id,$call_by_reference);
//
// Direct calls of Object methods removed completely!
//
/*
switch ($_GET["cmd"])
{
	default:
		$data = $obj->$method();
		break;
}*/

// CALL METHOD OF GUI OBJECT
$class_constr = "ilObj".$class_name."GUI";
require_once("./classes/class.ilObj".$class_name."GUI.php");
//echo "$class_constr().$method<br>"; //exit;
$obj = new $class_constr($data, $id, $call_by_reference);
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
