<?php
include_once "include/ilias_header.inc";

// Template-Engine anschmeissen
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_adm.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");

$tpl->setCurrentBlock("locator");
$tpl->setVariable("TREEPATH",$path);
$tpl->setVariable("PATH", $lng->txt("path"));
$tpl->parseCurrentBlock();

//show tabs
$o = array();
$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

// determine sort direction
if(!$_GET["direction"] || $_GET["direction"] == 'ASC')
{
	$tpl->setVariable("DIR",'DESC');
}
if($_GET["direction"] == 'DESC')
{
	$tpl->setVariable("DIR",'ASC');
}

// set sort column
if (empty($_GET["order"]))
{
	$_GET["order"] = "title";
}

if ($tree->getChilds($_GET["obj_id"],$_GET["order"],$_GET["direction"]))
{
	$num = 1;
	
	foreach ($tree->Childs as $key => $val)
    {
		$tpl->setCurrentBlock("row");
		// VISIBLE?
		if(!$rbacsystem->checkAccess("visible",$val["id"],$_GET["obj_id"]))
		{
			continue;
		}
		
		$num++;
		
		// color changing
		$css_row = TUtil::switchColor($num, "tblrow1", "tblrow2");
		
		$node = "[<a href=\"content.php?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
		
		$tpl->setCurrentBlock("row");
		$tpl->setVariable("LINK_TARGET","content.php?obj_id=".$val["id"]."&parent=".$val["parent"]);
		$tpl->setVariable("OBJ_TITLE",$val["title"]);
		$tpl->setVariable("OBJ_DESC",$val["desc"]);
		$tpl->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
		$tpl->setVariable("IMG_TYPE","icon_".$val["type"]."_b.gif");
		$tpl->setVariable("ALT_IMG_TYPE",$val["type"]);
		$tpl->setVariable("CSS_ROW",$css_row);
		$tpl->setVariable("OBJ_ID",$val["id"]);
		$tpl->parseCurrentBlock();
    }
	
	$tpl->touchBlock("options");
}
else
{
	$tpl->touchBlock("notfound");
}

// display category options
$type = $obj["type"];

if (!empty($ilias->typedefinition[$type]))
{
	$tpl->setCurrentBlock("type");
	$opts = TUtil::formSelect(12,"type",TUtil::getModules($ilias->typedefinition[$type]));
	$tpl->setVariable("SELECT_OBJTYPE",$opts);
	$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
	$tpl->setVariable("TPOS",$_GET["parent"]);
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("OBJ_EDIT","object.php?obj_id=".$_GET["obj_id"]."&parent".$_GET["parent"]."&type=admin");
$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
$tpl->setVariable("TPOS",$_GET["parent"]);

//show tabs
$o = array();
$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

if ($_GET["message"])
{
	$tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("MSG",stripslashes($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
$tpl->setCurrentBlock("systemsettings");
require_once("./include/inc.basicdata.php");
$tpl->parseCurrentBlock();

$tpl->show();

?>