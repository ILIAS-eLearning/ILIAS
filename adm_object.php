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

// CALL METHOD OF OBJECT
// e.g: cmd = 'view' type = 'frm'
// => $obj = new ForumObject(); $obj->viewObject() 
$class_name = $objDefinition->getClassName($type);
$class_constr = $class_name."Object";

require_once("./classes/class.".$class_name."Object.php");
$obj = new $class_constr();
$data = $obj->$method();


// CALL OUTPUT METHOD OF OBJECT
$class_constr = $class_name."ObjectOut";

require_once("./classes/class.".$class_name."ObjectOut.php");
$obj = new $class_constr($data);
$obj->$method();

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