<?php
require_once "include/ilias_header.inc";

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_usr.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

//show tabs
$o = array();
$o["LINK1"] = "content_user.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tpl->setCurrentBlock("locator");
$tpl->setVariable("TREEPATH",$path);
$tpl->setVariable("TXT_PATH", $lng->txt("path"));
$tpl->parseCurrentBlock();

$tpl->setVariable("TYPE","user");
$tpl->setVariable("MESSAGE","<h5>Click on the name of a user to edit that user</h5>");

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

// BEGIN ROW

if ($rbacsystem->checkAccess('read',$_GET["obj_id"],$_GET["parent"]))
{
	if ($user_data = getUserList($_GET["order"],$_GET["direction"]) )
	{
		foreach ($user_data as $key => $val)
		{
			// color changing
			$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");

			$node = "[<a href=\"".$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";

			$tpl->setCurrentBlock("row");
			$tpl->setVariable("LINK_TARGET","object.php?obj_id=".$val["obj_id"]."&parent=".$_GET["obj_id"]."&parent_parent=".$_GET["parent"]."&cmd=edit");
			$tpl->setVariable("OBJ_TITLE",$val["title"]);
			$tpl->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
			$tpl->setVariable("IMG_TYPE","icon_user_b.gif");
			$tpl->setVariable("ALT_IMG_TYPE","user");
			$tpl->setVariable("CSS_ROW",$css_row);
			$tpl->setVariable("OBJ",$val["obj_id"]);
			$tpl->parseCurrentBlock("row");
		}
		
		$tpl->touchBlock("options");
	}
}
else
{
	$ilias->raiseError("No permission to read user folder",$ilias->error_obj->MESSAGE);
}

if ($_GET["message"])
{
	$tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("MSG",stripslashes($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->show();

?>