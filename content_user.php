<?php
include_once "include/ilias_header.inc";


// Template generieren
$tplContent = new Template("content_user.html",true,true);

$tplContent->setVariable("OBJ_SELF","content_user.php?obj_id=$obj_id&parent=$parent");
$tplContent->setVariable("OBJ_ID",$obj_id);
$tplContent->setVariable("TPOS",$parent);

// display path
$tree = new Tree($obj_id,1,1);
$tree->getPath();
$path = showPath($tree->Path,"content.php");
$tplContent->setVariable("TREEPATH",$path);
$tplContent->setVariable("MESSAGE","<h5>Click on the name of a user to edit that user</h5>");
$tplContent->setVariable("TYPE","user");

// BEGIN ROW
$tplContent->setCurrentBlock("row",true);
// Wegen short circuit evaluation muss die Rechte Abfrage zuerst erfolgen
if($user_data = getUserList() )
{
	foreach($user_data as $key => $val)
	{
		// color changing
		if ($key % 2)
		{
			$css_row = "row_high";	
		}
		else
		{
			$css_row = "row_low";
		}

		$node = "[<a href=\"".$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
		$tplContent->setVariable("LINK_TARGET","object.php?obj_id=".$val["obj_id"]."&parent=$obj_id&cmd=edit");
		$tplContent->setVariable("OBJ_TITLE",$val["title"]);
		$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
		$tplContent->setVariable("IMG_TYPE","autor.gif");
		$tplContent->setVariable("ALT_IMG_TYPE","Category");
		$tplContent->setVariable("CSS_ROW",$css_row);
		$tplContent->setVariable("OBJ",$val["obj_id"]);
		$tplContent->parseCurrentBlock("row");
	}
	$tplContent->touchBlock("options");
}
else
{
	$tplContent->setCurrentBlock("notfound");
	$tplContent->setVariable("MESSAGE","No Permission to read");
	$tplContent->parseCurrentBlock();
}
if($_SESSION["Error_Message"])
{
	$tplContent->setVariable("ERROR",$_SESSION["Error_Message"]);
}

include_once "include/ilias_footer.inc";
?>