<?php
include_once "include/ilias_header.inc";
include_once "classes/class.Object.php";	// base class for all Object Types

if(!isset($_POST["type"]))
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
		include_once "classes/class.LearningObject.php";
		$obj = new LearningObject();
		$obj->$methode();
		break;

    case "frm":
		include_once "classes/class.ForumObject.php";
		$obj = new ForumObject();
		$obj->$methode();
		break;
    
    case "grp":
		include_once "classes/class.GroupObject.php";
		$obj = new GroupObject();
		$obj->$methode();
		break;

    case "cat":
		include_once "classes/class.CategoryObject.php";
		$obj = new CategoryObject();
		$obj->$methode();
		break;

    case "crs":
		include_once "classes/class.CourseObject.php";
		$obj = new CourseObject();
		$obj->$methode();
		break;
		
    case "role": 
		include_once "classes/class.RoleObject.php";
		$obj = new RoleObject();
		$obj->$methode();
		break;

    case "rolt": 
		include_once "classes/class.RoleTemplateObject.php";
		$obj = new RoleTemplateObject();
		$obj->$methode();
		break;

	case "rolf":
		include_once "classes/class.RoleFolderObject.php";
		$obj = new RoleFolderObject();
		$obj->$methode();
		break;

    case "user":
		include_once "classes/class.UserObject.php";
		$obj = new UserObject();
		$obj->$methode();
		break;

	case "usrf":
		include_once "classes/class.UserFolderObject.php";
		$obj = new UserFolderObject();
		$obj->$methode();
		break;
	
	case "type":
		include_once "classes/class.TypeDefinitionObject.php";
		$obj = new TypeDefinitionObject();
		$obj->$methode();
		break;

	case "objf":
		include_once "classes/class.ObjectFolderObject.php";
		$obj = new ObjectFolderObject();
		$obj->$methode();
		break;
    
	case "adm":
		include_once "classes/class.SystemFolderObject.php";
		$obj = new SystemFolderObject();
		$obj->$methode();
		break;

    default:
		$ilias->raiseError("Object type '".$type."' is not implemented yet.",$ilias->error_obj->MESSAGE);
		break;
}

include_once "include/ilias_footer.inc";
?>