<?php
require_once "include/ilias_header.inc";
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
	include_once ("classes/class.Admin.php");
	$obj2 = new Admin();
		
	switch ($_POST["cmd"])
	{
		case $lng->txt("cut"):
			$_GET["cmd"] = "cut";
			$methode = $_POST["cmd"]."Object"; 
			$result = $obj2->cutObject();
			break;
		case $lng->txt("copy"):
			$_GET["cmd"] = "copy";
			$methode = $_POST["cmd"]."Object"; 
			$result = $obj2->copyObject();
			break;
		case $lng->txt("paste"):
			$_GET["cmd"] = "paste";
			$methode = $_POST["cmd"]."Object"; 
			$result = $obj2->pasteObject();			
			break;
		case $lng->txt("delete"):
			$_GET["cmd"] = "delete";
			$result = $obj2->deleteObject();
			break;
		case $lng->txt("clear"):
			$_GET["cmd"] = "clear";
			$result = $obj2->clearObject();
			break;
	}
	header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
		   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
	exit();
}

//if no cmd is given default to first property
if ($_GET["cmd"] == "")
{
	$_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
}

if($_REQUEST["new_type"])
{
	$type = $_REQUEST["new_type"];
}
else
{
	$type = $_GET["type"];
}

$methode = $_GET["cmd"]."Object";
switch ($type)
{
	case "le":
		require_once "classes/class.LearningModuleObject.php";
		require_once "classes/class.LearningModuleObjectOut.php";
		
		$obj = new LearningModuleObject();

		$data = $obj->$methode();
		$out = new LearningModuleObjectOut($data);
		$out->$methode();
		break;

	case "frm":
		require_once "classes/class.ForumObject.php";
		require_once "classes/class.ForumObjectOut.php";

		$obj = new ForumObject();

		$data = $obj->$methode();
		$out = new ForumObjectOut($data);
		$out->$methode();
		break;
    
	case "grp":
		require_once "classes/class.GroupObject.php";
		require_once "classes/class.GroupObjectOut.php";

		$obj = new GroupObject();
		$data = $obj->$methode();
		$out = new GroupObjectOut($data);
		$out->$methode();
		break;

	case "cat":
		require_once "classes/class.CategoryObject.php";
		require_once "classes/class.CategoryObjectOut.php";

		$obj = new CategoryObject();
		$data = $obj->$methode();
		$out = new CategoryObjectOut($data);
		$out->$methode();
		break;

	case "crs":
		require_once "classes/class.CourseObject.php";
		require_once "classes/class.CourseObjectOut.php";

		$obj = new CourseObject();
		$data = $obj->$methode();
		$out = new CourseObjectOut($data);
		$out->$methode();
		break;
		
    case "role":
		require_once "classes/class.RoleObject.php";
		require_once "classes/class.RoleObjectOut.php";

		$obj = new RoleObject();
		$data = $obj->$methode();
		$out = new RoleObjectOut($data);
		$out->$methode();
		break;

    case "rolt": 
		require_once "classes/class.RoleTemplateObject.php";
		require_once "classes/class.RoleTemplateObjectOut.php";

		$obj = new RoleTemplateObject();
		$data = $obj->$methode();
		$out = new RoleTemplateObjectOut($data);
		$out->$methode();
		break;

	case "rolf":
		require_once "classes/class.RoleFolderObject.php";
		require_once "classes/class.RoleFolderObjectOut.php";

		$obj = new RoleFolderObject();
		$data = $obj->$methode();
		$out = new RoleFolderObjectOut($data);
		$out->$methode();
		break;

    case "usr":
		require_once "classes/class.UserObject.php";
		require_once "classes/class.UserObjectOut.php";
		$obj = new UserObject();
		$data = $obj->$methode();
		$out = new UserObjectOut($data);
		$out->$methode();
		break;

	case "usrf":
		require_once "classes/class.UserFolderObject.php";
		require_once "classes/class.UserFolderObjectOut.php";

		$obj = new UserFolderObject();
		$data = $obj->$methode();
		$out = new UserFolderObjectOut($data);
		$out->$methode();
		break;
	
	case "typ":
		require_once "classes/class.TypeDefinitionObject.php";
		require_once "classes/class.TypeDefinitionObjectOut.php";

		$obj = new TypeDefinitionObject();
		$data = $obj->$methode();
		$out = new TypeDefinitionObjectOut($data);
		$out->$methode();
		break;

	case "lngf":
		require_once "classes/class.LanguageFolderObject.php";
		require_once "classes/class.LanguageFolderObjectOut.php";

		$obj = new LanguageFolderObject();
		$data = $obj->$methode();
		$out = new LanguageFolderObjectOut($data);
		$out->$methode();
		break;

	case "lng":
		require_once "classes/class.LanguageObject.php";
		require_once "classes/class.LanguageObjectOut.php";

		$obj = new LanguageObject();
		$data = $obj->$methode();
		$out = new LanguageObjectOut($data);
		$out->$methode();
		break;
		
	case "objf":
		require_once "classes/class.ObjectFolderObject.php";
		require_once "classes/class.ObjectFolderObjectOut.php";

		$obj = new ObjectFolderObject();
		$data = $obj->$methode();
		$out = new ObjectFolderObjectOut($data);
		$out->$methode();
		break;
    
	case "adm":
		require_once "classes/class.SystemFolderObject.php";
		require_once "classes/class.SystemFolderObjectOut.php";

		$obj = new SystemFolderObject();
		$data = $obj->$methode();
		$out = new SystemFolderObjectOut($data);
		$out->$methode();
		break;

    default:
		$ilias->raiseError("Object type '".$type."' is not implemented yet.",$ilias->error_obj->MESSAGE);
		break;
}

if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", $_GET["message"]);
	$tpl->parseCurrentBlock();
}


if ($_GET["cmd"] == "view" && $obj->type == "adm")
{
	$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
	$tpl->setCurrentBlock("systemsettings");
	require_once("./include/inc.basicdata.php");
	$tpl->parseCurrentBlock();
}
$tpl->show();

?>