<?php
include_once "include/ilias_header.inc";

$obj = getObject($_GET["obj_id"]);

//  Type = usrf => Verzweige nach content_user.php
if($obj["type"] == 'usrf')
{
	header("Location: content_user.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&order=".$_GET["order"]."&direction=".$_GET["direction"]);
	exit;
}
// Type = rolf => Verzweige nach content_role.php
if($obj["type"] == 'rolf')
{
	header("Location: content_role.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&order=".$_GET["order"]."&direction=".$_GET["direction"]);
	exit;
}
// Type = objf => Verzweige nach content_type.php
if($obj["type"] == 'objf')
{
	header("Location: content_type.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&order=".$_GET["order"]."&direction=".$_GET["direction"]);
	exit;
}
// Type = objf => Verzweige nach content_type.php
if($obj["type"] == 'lngf')
{
	header("Location: content_lang.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&order=".$_GET["order"]."&direction=".$_GET["direction"]);
	exit;
}
// Type = adm => Verzweige nach content_adm.php
if($obj["type"] == 'adm')
{
	header("Location: content_adm.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&order=".$_GET["order"]."&direction=".$_GET["direction"]);
	exit;
}
//  Type = type => Verzweige nach content_type.php
if($obj["type"] == 'type')
{
	header("Location: content_operations.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"].
		   "&parent_parent=".$_GET["parent_parent"]."&order=".$_GET["order"]."&direction=".$_GET["direction"]);
	exit;
}

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

//administration content, could be table, or input form or just information
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_table.html");

//show tabs
$o = array();
$o["LINK1"] = "./content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

// show path, where am i?
$tpl->setCurrentBlock("locator");
$tpl->setVariable("TXT_PATH", $lng->txt("path"));
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tpl->setVariable("TREEPATH",$path);
$tpl->parseCurrentBlock();

// was a command submitted?
if (isset($_POST["cmd"]))
{
	$methode = $_POST["cmd"]."Object"; 
	include_once ("classes/class.Admin.php");
	$obj2 = new Admin();
	$obj2->$methode();
}

// show paste & clear buttons if something was cut or copied
if (!empty($clipboard))
{
	$tpl->touchBlock("btn_paste");
	$tpl->touchBlock("btn_clear");
}

// determine sort direction
if (!$_GET["direction"] || $_GET["direction"] == 'ASC')
{
	$tpl->setVariable("DIR",'DESC');
}

if ($_GET["direction"] == 'DESC')
{
	$tpl->setVariable("DIR",'ASC');
}

// set sort column
if (empty($_GET["order"]))
{
	$_GET["order"] = "title";
}

//$tplContent->setVariable("OBJ_SELF",substr(strrchr($REQUEST_URI, "/"), 1));
$tpl->setVariable("OBJ_SELF","content.php?obj_id=".$_GET["obj_id"]."parent=".$_GET["parent"]);

if ($tree->getChilds($_GET["obj_id"],$_GET["order"],$_GET["direction"]))
{
	$num = 1;
	
	foreach ($tree->Childs as $key => $val)
    {
		// VISIBLE?
		if (!$rbacsystem->checkAccess("visible",$val["id"],$val["parent"]))
		{
			continue;
		}
		
		$num++;
		
		// color changing
		$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");
		
		if ($val["type"] == "adm")
		{
			$checkbox = "&nbsp;";
		}
		else
		{
			$checkbox = "<input type=\"checkbox\" name=\"id[]\" value=\"".$val["id"]."\"/>\n";
		}

		$node = "[<a href=\"".$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";

		$tpl->setCurrentBlock("row");
		$tpl->setVariable("LINK_TARGET",$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]);
		$tpl->setVariable("OBJ_TITLE",$val["title"]);
		$tpl->setVariable("OBJ_DESC",$val["desc"]);
		$tpl->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
		$tpl->setVariable("IMG_TYPE","icon_".$val["type"]."_b.gif");
		$tpl->setVariable("ALT_IMG_TYPE",$val["type"]);
		$tpl->setVariable("CSS_ROW",$css_row);
		$tpl->setVariable("OBJ_ID",$val["id"]);
		$tpl->setVariable("CHECKBOX",$checkbox);
		$tpl->parseCurrentBlock();
    }

	//object functions: cut, copy, paste
	$tpl->touchBlock("options");
}
else
{
	$tpl->setCurrentBlock("notfound");
	$tpl->setVariable("TXT_OBJECT_NOT_FOUND", $lng->txt("object_not_found"));
}

// display category options
$type = $obj["type"];

//add new objects
if (!empty($ilias->typedefinition[$type]))
{
	// Show only objects with permission 'create'
	$objects = TUtil::getModules($ilias->typedefinition[$type]);
	
	foreach ($objects as $key => $object)
	{
		if ($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$key))
		{
			$createable[$key] = $object;
		}
	}

	if (count($createable))
	{
		$opts = TUtil::formSelect(12,"type",$createable);

		$tpl->addBlockFile("ADD_OBJ", "add_obj", "tpl.adm_add_obj.html");
		$tpl->setCurrentBlock("add_obj");
		$tpl->setVariable("SELECT_OBJTYPE", $opts);
		$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
		$tpl->setVariable("TPOS",$_GET["parent"]);
		$tpl->setVariable("TXT_ADD", $lng->txt("add"));
		$tpl->parseCurrentBlock();
	}
}

$tpl->setCurrentBlock("adm_content");
$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
$tpl->setVariable("TPOS",$_GET["parent"]);
$tpl->parseCurrentBlock("table");

if ($_GET["message"])
{
	$tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("MSG",stripslashes($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->show();

?>