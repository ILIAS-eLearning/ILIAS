<?php
include_once "include/ilias_header.inc";


// Template generieren
$tplContent = new Template("content_type.html",true,true);

$tplContent->setVariable("OBJ_SELF","content.php?obj_id=$obj_id&parent=$parent");
$tplContent->setVariable("OBJ_ID",$obj_id);
$tplContent->setVariable("TPOS",$parent);

// display path
$tree = new Tree($obj_id,1,1);
$path = showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);
$tplContent->setVariable("MESSAGE","<h5>Click on the name of a object type to edit that object type</h5>");

// BEGIN ROW
$tplContent->setCurrentBlock("row",true);

if($rbacsystem->checkAccess('read',$_GET["obj_id"],$_GET["parent"]))
{
	if ($type_data = getTypeList())
	{
		foreach($type_data as $key => $val)
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

			$node = "[<a href=\"content.php?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
			$tplContent->setVariable("LINK_TARGET","content.php?obj_id=".$val["obj_id"]."&parent=$obj_id");
			$tplContent->setVariable("OBJ_TITLE",$val["title"]);
			$tplContent->setVariable("OBJ_DESC",$val["desc"]);
			$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
			$tplContent->setVariable("IMG_TYPE","icon_type.gif");
			$tplContent->setVariable("ALT_IMG_TYPE","Object type");
			$tplContent->setVariable("CSS_ROW",$css_row);
			$tplContent->setVariable("OBJ",$val["obj_id"]);
			$tplContent->parseCurrentBlock("row");
		}
		$tplContent->touchBlock("options");
	}
}
else
{
	$ilias->raiseError("No permission to read object folder",$ilias->error_class->WARNING);
}
include_once "include/ilias_footer.inc";
?>