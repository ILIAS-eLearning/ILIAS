<?php
require_once "include/ilias_header.inc";

// Template generieren
$tplContent = new Template("content_user.html",true,true);

//deprecated???
$tplContent->setVariable("OBJ_SELF","content_user.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
$tplContent->setVariable("TPOS",$_GET["parent"]);

//show tabs
$o = array();
$o["LINK1"] = "content_user.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tplContent->setVariable("TABS", TUtil::showTabs(1,$o));


// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);
$tplContent->setVariable("MESSAGE","<h5>Click on the name of a user to edit that user</h5>");
$tplContent->setVariable("TYPE","user");

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

// BEGIN ROW
$tplContent->setCurrentBlock("row",true);

if ($rbacsystem->checkAccess('read',$_GET["obj_id"],$_GET["parent"]))
{
	if ($user_data = getUserList($_GET["order"],$_GET["direction"]) )
	{
		foreach ($user_data as $key => $val)
		{
			// color changing
			$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");

			$node = "[<a href=\"".$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
			$tplContent->setVariable("LINK_TARGET","object.php?obj_id=".$val["obj_id"]."&parent=".$_GET["obj_id"]."&parent_parent=".$_GET["parent"]."&cmd=edit");
			$tplContent->setVariable("OBJ_TITLE",$val["title"]);
			$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
			$tplContent->setVariable("IMG_TYPE","icon_user_b.gif");
			$tplContent->setVariable("ALT_IMG_TYPE","user");
			$tplContent->setVariable("CSS_ROW",$css_row);
			$tplContent->setVariable("OBJ",$val["obj_id"]);
			$tplContent->parseCurrentBlock("row");
		}
		
		$tplContent->touchBlock("options");
	}
}
else
{
	$ilias->raiseError("No permission to read user folder",$ilias->error_obj->MESSAGE);
}

if ($_GET["message"])
{
	$tplContent->setCurrentBlock("sys_message");
	$tplContent->setVariable("ERROR_MESSAGE",stripslashes($_GET["message"]));
	$tplContent->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT", $tplContent->get());	
$tplmain->show();

?>