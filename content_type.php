<?php
require_once "include/ilias_header.inc";

// Template generieren
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_type.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

//show tabs
$o = array();
$o["LINK1"] = "content_type.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
$tpl->setVariable("TPOS",$_GET["parent"]);
$tpl->setVariable("PAR",$_GET["parent_parent"]);

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tpl->setCurrentBlock("locator");
$tpl->setVariable("TREEPATH",$path);
$tpl->setVariable("TXT_PATH", $lng->txt("path"));
$tpl->parseCurrentBlock();

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

if ($rbacsystem->checkAccess('read',$_GET["obj_id"],$_GET["parent"]))
{
	if ($type_data = getTypeList($_GET["order"],$_GET["direction"]))
	{
		foreach ($type_data as $key => $val)
		{
			// BEGIN ROW
			$tpl->setCurrentBlock("row");

			// color changing
			$css_row = TUtil::switchColor($key,"tblrow1", "tblrow2");

			$node = "[<a href=\"content.php?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";

			$tpl->setCurrentBlock("row");
			$tpl->setVariable("LINK_TARGET","content.php?obj_id=".
									 $val["obj_id"]."&parent=$_GET[obj_id]&parent_parent=$_GET[parent]");
			$tpl->setVariable("OBJ_TITLE",$val["title"]);
			$tpl->setVariable("OBJ_DESC",$val["desc"]);
			$tpl->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
			$tpl->setVariable("IMG_TYPE","icon_type_b.gif");
			$tpl->setVariable("ALT_IMG_TYPE","type");
			$tpl->setVariable("CSS_ROW",$css_row);
			$tpl->setVariable("OBJ",$val["obj_id"]);
			$tpl->parseCurrentBlock();
		}
		$tpl->touchBlock("options");
	}
}
else
{
	$ilias->raiseError("No permission to read 'object' folder",$ilias->error_obj->MESSAGE);
}

if ($_GET["message"])
{
	$tpl->setCurrentBlock("sys_message");
	$tpl->setVariable("ERROR_MESSAGE",stripslashes($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->show();

?>