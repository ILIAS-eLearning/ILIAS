<?php
include_once "include/ilias_header.inc";

// on first start obj_id is set to 1
$obj_id = $obj_id ?  $obj_id : 1;

$obj = getObject($obj_id);

//  Type = usrf => Verzweige nach content_user.php
if($obj["type"] == 'usrf')
{
	header("Location: content_user.php?obj_id=$obj_id&parent=$parent");
}
// Type = rolf => Verzweige nach content_role.php
if($obj["type"] == 'rolf')
{
	header("Location: content_role.php?obj_id=$obj_id&parent=$parent");
}
// Type = objf => Verzweige nach content_type.php
if($obj["type"] == 'objf')
{
	header("Location: content_type.php?obj_id=$obj_id&parent=$parent");
}
// Type = adm => Verzweige nach content_adm.php
if($obj["type"] == 'adm')
{
	header("Location: content_adm.php?obj_id=$obj_id&parent=$parent");
}
//  Type = type => Verzweige nach content_type.php
if($obj["type"] == 'type')
{
	header("Location: content_operations.php?obj_id=$obj_id&parent=$parent");
}


// Template-Engine anschmeissen
$tplContent = new Template("content_main.html",true,true);
// create tree object: if $pos is not set use root id
$tree =& new Tree($obj_id,1,1);

// display path
$tree->getPath();
$path = showPath($tree->Path,"content.php");
$tplContent->setVariable("TREEPATH",$path);
//$tplContent->setVariable("OBJ_SELF",substr(strrchr($REQUEST_URI, "/"), 1));
$tplContent->setVariable("OBJ_SELF","content.php?parent=$parent&obj_id=$obj_id");

$tplContent->setCurrentBlock("row",true);

$rbacsystem = new RbacSystemH($ilias->db);
if ($tree->getChilds())
{
	$zaehler = 0;
	
	foreach ($tree->Childs as $key => $val)
    {
		// VISIBLE?
		if(!$rbacsystem->checkAccess("visible"))
		{
			continue;
		}
		
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
		
		if ($val["type"] == "adm")
		{
			$checkbox = "&nbsp;";
		}
		else
		{
			$checkbox = "<input type=\"checkbox\" name=\"id[]\" value=\"".$val["id"]."\"/>\n";
		}

		$node = "[<a href=\"".$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
		$tplContent->setVariable("LINK_TARGET",$SCRIPT_NAME."?obj_id=".$val["id"]."&parent=".$val["parent"]);
		$tplContent->setVariable("OBJ_TITLE",$val["title"]);
		$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
		$tplContent->setVariable("IMG_TYPE","icon_".$val["type"].".gif");
		$tplContent->setVariable("ALT_IMG_TYPE","Category");
		$tplContent->setVariable("CSS_ROW",$css_row);
		$tplContent->setVariable("OBJ_ID",$val["id"]);
		$tplContent->setVariable("CHECKBOX",$checkbox);
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
$tplContent->setVariable("OBJ_ID",$obj_id);
$tplContent->setVariable("TPOS",$parent);
include_once "include/ilias_footer.inc";
?>