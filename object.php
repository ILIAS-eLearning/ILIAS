<?php

include_once "include/ilias_header.inc";
// create tree object: if $pos is not set use root id
$tree =& new Tree($obj_id,1,1);
// display path
$tree->getPath();

$path = showPath($tree->Path,"content.php");

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
		$obj = new LearningObject($ilias);
		$obj->$methode();
		break;

    case "frm":
		include_once "classes/class.ForumObject.php";
		$obj = new ForumObject($ilias);
		$obj->$methode();
		break;
    
    case "grp":
		include_once "classes/class.GroupObject.php";
		$obj = new GroupObject($ilias);
		$obj->$methode();
		break;

    case "cat":
		include_once "classes/class.CategoryObject.php";
		$obj = new CategoryObject($ilias);
		$obj->$methode();
		break;

    case "role": 
		include_once "classes/class.RoleObject.php";
		$obj = new RoleObject($ilias);
		$obj->$methode();
		break;
    
    case "user":
		include_once "classes/class.UserObject.php";
		$obj = new UserObject($ilias);
		$obj->$methode();
		break;

	case "usrf":
		include_once "classes/class.UserFolderObject.php";
		$obj = new UserFolderObject($ilias);
		$obj->$methode();
		break;
		
	case "rolf":
		include_once "classes/class.RoleFolderObject.php";
		$obj = new RoleFolderObject($ilias);
		$obj->$methode();
		break;

	case "admin":
		include_once ("classes/class.Admin.php");
		$obj = new Admin($ilias);
		$obj->$methode();
		break;

		/*
		  case "kurs":
		  include_once ("include/kurs.inc");
		  break;

		  case "file":
		  include_once ("include/file.inc");
		  break;

		  case "set":
		  include_once ("include/set.inc");
		  break;

		  case "abo":
		  include_once ("include/abo.inc");
		  break;
    
		  case "adm":
		  include_once ("include/adm.inc");
		  break;

		  case "none":
		  include_once ("include/none.inc");
		  break;
		*/
		
	case "type":
		include_once "classes/class.TypeDefinitionObject.php";
		$obj = new TypeDefinitionObject($ilias);
		$obj->$methode();
		break;

	case "objf":
		include_once "classes/class.ObjectFolderObject.php";
		$obj = new ObjectFolderObject($ilias);
		$obj->$methode();
		break;
    
    default:
		$sys_message = "Objekttyp <i>".$type."</i> nocht nicht implementiert";
		header("Location: browser.php?sys_message=".urlencode($sys_message));
		break;
}

include_once "include/ilias_footer.inc";
?>