<?php
include_once "include/ilias_header.inc";

// on first start obj_id is set to 1
$obj_id = $obj_id ?  $obj_id : 1;

// Template-Engine anschmeissen
$tplContent = new Template("content_adm.html",true,true);
// create tree object: if $pos is not set use root id
$tree =& new Tree($obj_id,1,1);

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);
//$tplContent->setVariable("OBJ_SELF",substr(strrchr($REQUEST_URI, "/"), 1));
$tplContent->setVariable("OBJ_SELF","content.php?parent=$parent&obj_id=$obj_id");

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

$rbacsystem = new RbacSystemH($ilias->db);
if ($tree->getChilds($_GET["obj_id"],$_GET["order"],$_GET["direction"]))
{
	$zaehler = 0;
	
	foreach ($tree->Childs as $key => $val)
    {
		// VISIBLE?
//		if(!$rbacsystem->checkAccess($val["id"],"visible"))
//		{
//			continue;
//		}
		
		$zaehler++;
		
		// color changing
		if (!($zaehler % 2))
		{
			$css_row = "row_high";	
		}
		else
		{
			$css_row = "row_low";
		}
		
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
	$tplContent->setVariable("OBJ_ID",$obj_id);
	$tplContent->setVariable("TPOS",$parent);
	$tplContent->parseCurrentBlock("opt_type","type",true);
}
$tplContent->setVariable("OBJ_EDIT","object.php?parent=$parent&obj_id=$obj_id&type=admin");
$tplContent->setVariable("OBJ_ID",$obj_id);
$tplContent->setVariable("TPOS",$parent);

if($_GET["message"])
{
	$tplContent->setCurrentBlock("sys_message");
	$tplContent->setVariable("ERROR_MESSAGE",$_GET["message"]);
	$tplContent->parseCurrentBlock();
}

include_once "include/ilias_footer.inc";
?>