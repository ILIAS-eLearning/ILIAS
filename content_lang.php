<?php
require_once "include/ilias_header.inc";

// Template generieren
$tplContent = new Template("content_lang.html",true,true);

$tplContent->setVariable("OBJ_SELF","content_lang.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
$tplContent->setVariable("TPOS",$_GET["parent"]);

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);
$tplContent->setVariable("MESSAGE","<h5>Click on a language to install/deinstall this language</h5>");
$tplContent->setVariable("TYPE","lang");

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
	if ($lang_data = getLangList($_GET["order"],$_GET["direction"]) )
	{
		foreach ($user_data as $key => $val)
		{
			// color changing
			$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");

			$node = "[<a href=\"".$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
			$tplContent->setVariable("LINK_TARGET","object.php?obj_id=".$val["obj_id"]."&parent=".$_GET["obj_id"]."&parent_parent=".$_GET["parent"]."&cmd=edit");
			$tplContent->setVariable("OBJ_TITLE",$val["title"]);
			$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
			$tplContent->setVariable("IMG_TYPE","icon_lang_b.gif");
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