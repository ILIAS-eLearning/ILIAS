<?php
require_once "include/ilias_header.inc";
require_once "classes/class.Object.php";	// base class for all Object Types


if ($_POST["type"])
{
	$_GET["type"] = $_POST["type"];
}

if (!isset($_GET["type"]))
{
    $obj = getObject($_GET["obj_id"]);
    $_GET["type"] = $obj["type"];
}

//prepare output of administration view
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

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
			$obj2->cutObject();
			break;
		case $lng->txt("copy"):
			$_GET["cmd"] = "copy";
			$methode = $_POST["cmd"]."Object"; 
			$obj2->copyObject();
			break;
		case $lng->txt("paste"):
			$_GET["cmd"] = "paste";
			$methode = $_POST["cmd"]."Object"; 
			$obj2->pasteObject();			
			break;
		case $lng->txt("delete"):
			$_GET["cmd"] = "delete";
			$obj2->deleteObject();
			break;
		case $lng->txt("clear"):
			$_GET["cmd"] = "clear";
			$obj2->clearObject();
			break;
	}
	header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
}

$objData = $ilias->getObjDefinition($_GET["type"]);

//if no cmd is given default to first property
if ($_GET["cmd"] == "")
	$_GET["cmd"] = $objData["properties"][0]["attrs"]["CMD"];

$methode = $_GET["cmd"]."Object";

switch ($_GET["type"])
{
    case "le":
		require_once "classes/class.LearningObject.php";
		$obj = new LearningObject();
		$data = $obj->$methode();
		break;

    case "frm":
		require_once "classes/class.ForumObject.php";
		$obj = new ForumObject();
		$data = $obj->$methode();
		break;
    
    case "grp":
		require_once "classes/class.GroupObject.php";
		$obj = new GroupObject();
		$data = $obj->$methode();
		break;

    case "cat":
		require_once "classes/class.CategoryObject.php";
		$obj = new CategoryObject();
		$data = $obj->$methode();
		break;

    case "crs":
		require_once "classes/class.CourseObject.php";
		$obj = new CourseObject();
		$data = $obj->$methode();
		break;
		
    case "role": 
		require_once "classes/class.RoleObject.php";
		$obj = new RoleObject();
		$data = $obj->$methode();
		break;

    case "rolt": 
		require_once "classes/class.RoleTemplateObject.php";
		$obj = new RoleTemplateObject();
		$data = $obj->$methode();
		break;

	case "rolf":
		require_once "classes/class.RoleFolderObject.php";
		$obj = new RoleFolderObject();
		$data = $obj->$methode();
		break;

    case "usr":
		require_once "classes/class.UserObject.php";
		$obj = new UserObject();
		$data = $obj->$methode();
		break;

	case "usrf":
		require_once "classes/class.UserFolderObject.php";
		$obj = new UserFolderObject();
		$data = $obj->$methode();
		break;
	
	case "typ":
		require_once "classes/class.TypeDefinitionObject.php";
		$obj = new TypeDefinitionObject();
		$data = $obj->$methode();
		break;

	case "lngf":
		require_once "classes/class.LanguageFolderObject.php";
		$obj = new LanguageFolderObject();
		$data = $obj->$methode();
		break;

	case "lang":
		require_once "classes/class.LanguageObject.php";
		$obj = new LanguageObject();
		$data = $obj->$methode();
		break;
		
	case "objf":
		require_once "classes/class.ObjectFolderObject.php";
		$obj = new ObjectFolderObject();
		$data = $obj->$methode();
		break;
    
	case "adm":
		require_once "classes/class.SystemFolderObject.php";
		$obj = new SystemFolderObject();
		$data = $obj->$methode();
		break;

    default:
		$ilias->raiseError("Object type '".$type."' is not implemented yet.",$ilias->error_obj->MESSAGE);
		break;
}

//*************************admin tabs***********************+
$tabs = array();
$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
foreach ($objData["properties"] as $row)
	$tabs[] = array($row["name"], $row["attrs"]["CMD"]);

foreach ($tabs as $row)
{
	$i++;
	if ($row[1] == $_GET["cmd"])
	{
    	$tabtype = "tabactive";
		$tab = $tabtype;
	}
	else
	{
		$tabtype = "tabinactive";
		$tab = "tab";
	}

	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE", $tabtype);
	$tpl->setVariable("TAB_TYPE2", $tab);
	$tpl->setVariable("TAB_LINK", "adm_object.php?obj_id=".$obj->id."&parent=".$obj->parent."&parent_parent=".$obj->parent_parent."&cmd=".$row[1]);
	$tpl->setVariable("TAB_TEXT", $lng->txt($row[0]));
	$tpl->parseCurrentBlock();
}	

//****************** locator ********************
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
if ($obj->parent_parent != "")
	$path = $tree->getPathFull($obj->parent, $obj->parent_parent);
else
	$path = $tree->getPathFull($obj->id, $obj->parent);

//check if object isn't in tree, this is the case if parent_parent is set
if ($obj->parent_parent != "")
{
	$path[] = array(
		"id"	 => $_GET["obj_id"],
		"title"  => $obj->title,
		"parent" => $_GET["parent"],
		"parent_parent" => $_GET["parent_parent"]
	);
}

foreach ($path as $key => $row)
{
	if ($key < count($path)-1)
	{
	    $tpl->touchBlock("locator_separator");
	}
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $row["title"]);
	$tpl->setVariable("LINK_ITEM", "adm_object.php?obj_id=".$row["id"]."&parent=".$row["parent"]."&parent_parent=".$row["parent_parent"]);
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("locator");
$tpl->setVariable("TXT_PATH", "DEBUG: <font color=\"red\">".$_GET["type"]."::".$methode."</font><br>".$lng->txt("path"));
$tpl->parseCurrentBlock();

//****************content of object **********************************

if ($_GET["cmd"] != "create")
    $tplpart = $_GET["cmd"];
else
	$tplpart = "edit";
	
$template = "tpl.".$obj->type."_".$tplpart.".html";
if ($tpl->fileExists($template) == false)
{
	$template = "tpl.obj_".$tplpart.".html";
}

$tpl->addBlockFile("ADM_CONTENT", "adm_content", $template);

switch($_GET["cmd"])
{
	case "save":
	case "addPermission":
	case "permSave":
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
		exit();
		break;
		
	case "create":
		$tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$obj->id."&parent=".$obj->parent."&parent_parent=".$obj->parent_parent."&cmd=save&type=".$_GET["type"]);
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_REQUIRED_FLD", $lng->txt("required_field"));
		$tpl->setVariable("OBJ_TITLE", $data["title"]);
		$tpl->setVariable("OBJ_DESC", $data["desc"]);
		break;
		
	case "edit":

		foreach ($data["fields"] as $key => $val)
		{
			$tpl->setVariable("TXT_".strtoupper($key), $lng->txt($key));
			$tpl->setVariable(strtoupper($key), $val);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("FORMACTION", "adm_objects.php?type=".$obj->type."&cmd=update&obj_id=".$obj->id."&parent=".$obj->parent."&parent_parent=".$obj->parent_parent);
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_REQUIRED_FLD", $lng->txt("required_field"));
		$tpl->setVariable("OBJ_TITLE", $data["title"]);
		$tpl->setVariable("OBJ_DESC", $data["desc"]);
		break;

	case "owner":
		$tpl->setVariable("OWNER_NAME", $data);
		$tpl->setVariable("TXT_OBJ_OWNER", $lng->txt("obj_owner"));
		$tpl->setVariable("CMD","update");
		$tpl->parseCurrentBlock();
		break;

	case "perm":
		$tpl->setCurrentBlock("tableheader");
		$tpl->setVariable("TXT_PERMISSION", $lng->txt("permission"));
		$tpl->setVariable("TXT_ROLES", $lng->txt("roles"));
		$tpl->parseCurrentBlock();
		
		for ($i = 0; $i<count($data["rolenames"]); $i++)
		{
			$num++;
			$tpl->setCurrentBlock("ROLENAMES");
			$tpl->setVariable("ROLE_NAME",$data["rolenames"][$i]);
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBLock("CHECK_INHERIT");
			$tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$i]);
			$tpl->parseCurrentBlock();
			
			foreach ($data["permission"][$i]["values"] as $row)
			{
				$tpl->setCurrentBlock("CHECK_PERM");
				$tpl->setVariable("CHECK_PERMISSION",$row);
				$tpl->parseCurrentBlock();			
			}

			$tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = TUtil::switchColor($num, "tblrow1", "tblrow2");
			$tpl->setVariable("CSS_ROW",$css_row);
			$tpl->setVariable("PERMISSION", $data["permission"][$i]["name"]);
			$tpl->parseCurrentBlock();			

		}
		if ($data["local_role"] != "")
		{
			// ADD LOCAL ROLE
			$tpl->setCurrentBlock("LOCAL_ROLE");
			$tpl->setVariable("TXT_ADD", $lng->txt("add"));
			$tpl->setVariable("MESSAGE_BOTTOM", $lng->txt("you_may_add_local_roles"));
			$tpl->setVariable("LR_OBJ_ID",$data["local_role"]["id"]);
			$tpl->setVariable("LR_TPOS",$data["local_role"]["parent"]);
			$tpl->parseCurrentBlock();
		}
		break;

	case "view": 
	default:
		$num = 0;
		//table header
		foreach ($obj->objectList["cols"] as $key)
		{
			$tpl->setCurrentBlock("table_header_cell");
			if ($key != "")
			    $out = $lng->txt($key);
			else
				$out = "&nbsp;";
			$tpl->setVariable("TEXT", $out);
			$tpl->setVariable("LINK", "adm_object.php?obj_id=".$this->id."&parent=".$this->parent."&parent_parent=".$this->parent_parent."&order=type&direction=".$_GET["dir"]."&cmd=".$_GET["cmd"]);
			$tpl->parseCurrentBlock();
		}
		$tpl->setCurrentBlock("table_header_row");
		$tpl->parseCurrentBlock();
		
		if (is_array($obj->objectList["data"][0]))
		{
			//table cell
			for ($i=0; $i< count($obj->objectList["data"]); $i++)
			{
				$data = $obj->objectList["data"][$i];
				$ctrl = $obj->objectList["ctrl"][$i];
		
				$num++;
		
				// color changing
				$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");
			
				//checkbox
				if ($ctrl["type"] == "adm")
				{
					$tpl->touchBlock("empty_cell");
				}
				else
				{
					$tpl->setCurrentBlock("checkbox");
					$tpl->setVariable("CHECKBOX_ID", $ctrl["id"]);
					$tpl->setVariable("CSS_ROW", $css_row);
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("table_cell");
				$tpl->setVariable("TEXT", "");
				$tpl->parseCurrentBlock();
			
				//data
				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?";
					
					foreach ($ctrl as $key => $val2)
					{
						$link .= $key."=".$val2;
						if ($key != $ctrl[count($ctrl)-1][$key])
					    	$link .= "&";
					}
					$tpl->setCurrentBlock("begin_link");
					$tpl->setVariable("LINK_TARGET", $link);
					$tpl->parseCurrentBlock();
					$tpl->touchBlock("end_link");
			
					$tpl->setCurrentBlock("text");
					$tpl->setVariable("TEXT_CONTENT", $val);
					$tpl->parseCurrentBlock();
				
					$tpl->setCurrentBlock("table_cell");
					$tpl->parseCurrentBlock();

				} //foreach
				
				$tpl->setCurrentBlock("table_row");	
				$tpl->setVariable("CSS_ROW", $css_row);
				$tpl->parseCurrentBlock();
			} //for
		} //if is_array
		else
		{
			$tpl->setCurrentBlock("notfound");
			$tpl->setVariable("TXT_OBJECT_NOT_FOUND", $lng->txt("obj_not_found"));
		}
		
		//****************allowed operations on objects in current object*********************
		//forbidden operations
		$notoperations = array();
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";
		}

		$operations = array();

		foreach ($objData["actions"] as $row)
		{
			if (!in_array($row, $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations)>0) {
			foreach ($operations as $op)
			{
				$tpl->setCurrentBlock("operation_btn");
				$tpl->setVariable("BTN_VALUE", $lng->txt($op));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("operation");
			$tpl->parseCurrentBlock();
		}		
		
		//***************allowed subobjects ****************************
		
		//$data = $obj->getSubObjects();
		foreach ($objData["subobjects"] as $row)
		{
			//@todo max value abfragen und entsprechend evtl aus der liste streichen
		    $count = 0;
			if ($row["attrs"]["MAX"] > 0)
			{
				//how many elements are present?
				for ($i=0; $i<count($obj->objectList["ctrl"]); $i++)
				{
					if ($obj->objectList["ctrl"][$i]["type"] == $row["name"])
					{
					    $count++;
					}
				}
			}
			if ($row["attrs"]["MAX"] == "" || $count < $row["attrs"]["MAX"])
			{
				$subobj[] = $row["name"];
			}
		}		

		
		if (is_array($subobj))
		{
			//build form
			$opts = TUtil::formSelect(12,"type",$subobj);
	
			$tpl->setCurrentBlock("add_obj");
			$tpl->setVariable("SELECT_OBJTYPE", $opts);
			$tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&obj_id=".$obj->id."&parent=".$obj->parent."&parent_parent=".$obj->parent_parent);
			$tpl->setVariable("TXT_ADD", $lng->txt("add"));
			$tpl->parseCurrentBlock();
		}
		
		
} // switch

if ($_GET["cmd"] == "view" && $obj->type == "adm")
{
	$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
	$tpl->setCurrentBlock("systemsettings");
	require_once("./include/inc.basicdata.php");
	$tpl->parseCurrentBlock();
}

$tpl->show();

?>