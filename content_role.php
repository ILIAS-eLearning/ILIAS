<?php
require_once "include/ilias_header.inc";

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_rolf.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

//show tabs
$o = array();
$o["LINK1"] = "content_role.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");

$tpl->setCurrentBlock("locator");
$tpl->setVariable("TXT_PATH", $lng->txt("path"));
$tpl->setVariable("TREEPATH",$path);
$tpl->parseCurrentBlock();

$tpl->setVariable("MESSAGE","<h5>Click on the name of a role to edit the template of that role</h5>");
$tpl->setVariable("TYPE","role");

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

if ($rbacsystem->checkAccess('read',$_GET["obj_id"],$_GET["parent"]))
{
	if ($role_list = $rbacadmin->getRoleAndTemplateListByObject($obj_id,$_GET["order"],$_GET["direction"]))
	{
		foreach ($role_list as $key => $val)
		{
			// BEGIN ROW
			$tpl->setCurrentBlock("row");
			// color changing
			$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");

			$node = "[<a href=\"content.php?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
			$tpl->setVariable("LINK_TARGET","object.php?obj_id=".$val["obj_id"].
									 "&parent=$obj_id&parent_parent=$_GET[parent]&cmd=perm&show=rolf");
			$tpl->setVariable("OBJ_TITLE",$val["title"]);
			$tpl->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);

			// determine image (role object or role template?)
			$image = $val["type"] == 'rolt' ? 'icon_rtpl_b.gif' : 'icon_role_b.gif';

			$tpl->setVariable("IMG_TYPE",$image);
			$tpl->setVariable("ALT_IMG_TYPE",$val["type"]);

			$tpl->setVariable("CSS_ROW",$css_row);
			$tpl->setVariable("OBJ",$val["obj_id"]);
			$tpl->parseCurrentBlock();
		}
	}
}
else
{
	$ilias->raiseError("No permission to read role folder",$ilias->error_obj->MESSAGE);
}

if ($_GET["message"])
{
	$tpl->setCurrentBlock("sys_message");
	$tpl->setVariable("ERROR_MESSAGE",stripslashes($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TPLPATH", $tpl->tplPath);

$tpl->setVariable("CONTENT", $tpl->get());	
$tpl->show();

?>