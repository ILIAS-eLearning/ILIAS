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

//command should be get Parameter
//if there is a post-parameter it is translated, cause it is a buttonvalue
if ($_POST["cmd"] != "")
{
	switch ($_POST["cmd"])
	{
		case $lng->txt("cut"):
			$_GET["cmd"] = "cutAdm";
			break;
		case $lng->txt("copy"):
			$_GET["cmd"] = "copyAdm";
			break;
		case $lng->txt("link"):
			$_GET["cmd"] = "linkAdm";
			break;
		case $lng->txt("paste"):
			$_GET["cmd"] = "pasteAdm";
			break;
		case $lng->txt("clear"):
			$_GET["cmd"] = "clearAdm";
			break;
		case $lng->txt("delete"):
			$_GET["cmd"] = "confirmDeleteAdm";
			break;
		case $lng->txt("cancel"):
			$_GET["cmd"] = "cancelDelete";
			break;
		case $lng->txt("confirm"):
			$_GET["cmd"] = "deleteAdm";
			break;
		case $lng->txt("import"):
			$_GET["cmd"] = "import";
			break;
		case $lng->txt("export"):
			$_GET["cmd"] = "export";
			break;
		case $lng->txt("install"):
			$_GET["cmd"] = "install";
			break;
		case $lng->txt("uninstall"):
			$_GET["cmd"] = "uninstall";
			break;
		case $lng->txt("refresh"):
			$_GET["cmd"] = "refresh";
			break;
		case $lng->txt("set_system_language"):
			$_GET["cmd"] = "setsyslang";
			break;
		case $lng->txt("change_language"):
			$_GET["cmd"] = "setuserlang";
			break;
		case $lng->txt("check_language"):
			$_GET["cmd"] = "checklang";
			break;
	}
}

//if no cmd is given default to first property
if (!$_GET["cmd"])
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