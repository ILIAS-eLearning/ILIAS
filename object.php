<?php
require_once "include/ilias_header.inc";
require_once "classes/class.Object.php";	// base class for all Object Types

if (!isset($_POST["type"]))
{
    $obj = getObject($_GET["obj_id"]);
    $type = $obj["type"];
}
else
{
	//echo "no parent!";
}

$methode = $cmd."Object"; 

switch ($type)
{
    case "le":
		require_once "classes/class.LearningObject.php";
		$obj = new LearningObject();
		$obj->$methode();
		break;

    case "frm":
		require_once "classes/class.ForumObject.php";
		$obj = new ForumObject();
		$obj->$methode();
		break;
    
    case "grp":
		require_once "classes/class.GroupObject.php";
		$obj = new GroupObject();
		$obj->$methode();
		break;

    case "cat":
		require_once "classes/class.CategoryObject.php";
		$obj = new CategoryObject();
		$obj->$methode();
		break;

    case "crs":
		require_once "classes/class.CourseObject.php";
		$obj = new CourseObject();
		$obj->$methode();
		break;
		
    case "role": 
		require_once "classes/class.RoleObject.php";
		$obj = new RoleObject();
		$obj->$methode();
		break;

    case "rolt": 
		require_once "classes/class.RoleTemplateObject.php";
		$obj = new RoleTemplateObject();
		$obj->$methode();
		break;

	case "rolf":
		require_once "classes/class.RoleFolderObject.php";
		$obj = new RoleFolderObject();
		$obj->$methode();
		break;

    case "user":
		require_once "classes/class.UserObject.php";
		$obj = new UserObject();
		$obj->$methode();
		break;

	case "usrf":
		require_once "classes/class.UserFolderObject.php";
		$obj = new UserFolderObject();
		$obj->$methode();
		break;
	
	case "type":
		require_once "classes/class.TypeDefinitionObject.php";
		$obj = new TypeDefinitionObject();
		$obj->$methode();
		break;

	case "objf":
		require_once "classes/class.ObjectFolderObject.php";
		$obj = new ObjectFolderObject();
		$obj->$methode();
		break;
    
	case "adm":
		require_once "classes/class.SystemFolderObject.php";
		$obj = new SystemFolderObject();
		$obj->$methode();
		break;

	case "lngf":
		require_once "classes/class.LanguageFolderObject.php";
		$obj = new LanguageFolderObject();
		$obj->$methode();
		break;

	case "lang":
		require_once "classes/class.LanguageObject.php";
		$obj = new LanguageObject();
		$obj->$methode();
		break;

    default:
		$ilias->raiseError("Object type '".$type."' is not implemented yet.",$ilias->error_obj->MESSAGE);
		break;
}

$tplmain->setVariable("PAGECONTENT", $tplContent->get());	
$tplmain->show();

?>