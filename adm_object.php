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

if (!isset($_GET["type"]))
{
	$obj = getObject($_GET["obj_id"]);
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
	$type = $_REQUEST["new_type"];
}
else
{
	$type = $_GET["type"];
}
$method = $_GET["cmd"]."Object";


// build object instance
// e.g: cmd = 'view' type = 'frm'
// => $obj = new ForumObject(); $obj->viewObject() 
$class_name = $objDefinition->getClassName($type);
$class_constr = $class_name."Object";
require_once("./classes/class.".$class_name."Object.php");
$obj = new $class_constr($_GET["obj_id"]);

//echo "$class_constr().$method<br>";

// call object method
switch ($_GET["cmd"])
{
	// view object
	case "view":
		$data = $obj->viewObject($_GET["order"], $_GET["direction"]);
		break;

	// save object
	case "save":
		$data = $obj->saveObject($_GET["obj_id"], $_GET["parent"], $_GET["type"], $_GET["new_type"], $_POST["Fobject"]);
		break;

	// update object
	case "update":
		$data = $obj->updateObject($_POST["Fobject"]);
		break;
	
	// edit object
	case "edit":
		$data = $obj->editObject($_GET["order"], $_GET["direction"]);
		break;

	// edit object
	case "create":
		$data = $obj->createObject($_GET["obj_id"], $_POST["new_type"]);
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
	

		
	default:
		$data = $obj->$method();
		break;
}



// CALL OUTPUT METHOD OF OBJECT
$class_constr = $class_name."ObjectOut";

require_once("./classes/class.".$class_name."ObjectOut.php");
$obj = new $class_constr($data);
$obj->$method();
//echo "$class_constr().$method<br>";

// display basicdata formular
// TODO: must be changed for clientel processing
if ($_GET["cmd"] == "view" && $type == "adm")
{
	$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
	$tpl->setCurrentBlock("systemsettings");
	require_once("./include/inc.basicdata.php");
	$tpl->parseCurrentBlock();
}
$tpl->show();
?>
