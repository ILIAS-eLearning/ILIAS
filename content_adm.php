<?php
include_once "include/ilias_header.inc";

// Template-Engine anschmeissen
$tplContent = new Template("content_adm.html",true,true);

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);
//$tplContent->setVariable("OBJ_SELF",substr(strrchr($REQUEST_URI, "/"), 1));
$tplContent->setVariable("OBJ_SELF","content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);

$tplContent->setCurrentBlock("row",true);

// determine sort direction
if(!$_GET["direction"] || $_GET["direction"] == 'ASC')
{
	$tplContent->setVariable("DIR",'DESC');
}
if($_GET["direction"] == 'DESC')
{
	$tplContent->setVariable("DIR",'ASC');
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
		// VISIBLE?
		if(!$rbacsystem->checkAccess("visible",$val["id"],$_GET["obj_id"]))
		{
			continue;
		}
		
		$num++;
		
		// color changing
		$css_row = TUtil::switchColor($num,"row_high","row_low");
		
		$node = "[<a href=\"content.php?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
		$tplContent->setVariable("LINK_TARGET","content.php?obj_id=".$val["id"]."&parent=".$val["parent"]);
		$tplContent->setVariable("OBJ_TITLE",$val["title"]);
		$tplContent->setVariable("OBJ_DESC",$val["desc"]);
		$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
		$tplContent->setVariable("IMG_TYPE","icon_".$val["type"]."_b.gif");
		$tplContent->setVariable("ALT_IMG_TYPE",$val["type"]);
		$tplContent->setVariable("CSS_ROW",$css_row);
		$tplContent->setVariable("OBJ_ID",$val["id"]);
		$tplContent->parseCurrentBlock();
    }
	$tplContent->touchBlock("options");
}
else
{
	$tplContent->touchBlock("notfound");
}

// display category options
$type = $obj["type"];

if (!empty($ilias->typedefinition[$type]))
{
	$tplContent->setCurrentBlock("type");
	$opts = TUtil::formSelect(12,"type",TUtil::getModules($ilias->typedefinition[$type]));
	$tplContent->setVariable("SELECT_OBJTYPE",$opts);
	$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
	$tplContent->setVariable("TPOS",$_GET["parent"]);
	$tplContent->parseCurrentBlock("opt_type","type",true);
}

$tplContent->setVariable("OBJ_EDIT","object.php?obj_id=".$_GET["obj_id"]."&parent".$_GET["parent"]."&type=admin");
$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
$tplContent->setVariable("TPOS",$_GET["parent"]);

if ($_GET["message"])
{
	$tplContent->setCurrentBlock("sys_message");
	$tplContent->setVariable("ERROR_MESSAGE",stripslashes($_GET["message"]));
	$tplContent->parseCurrentBlock();
}

$eingebunden = true;
require_once("./adm_basicdata.php");
$tplContent->setVariable("SYSTEMSETTINGS",$tpl->get());

$tplContent->show();
?>