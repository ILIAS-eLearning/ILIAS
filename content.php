<?php
include_once "include/ilias_header.inc";

// on first start obj_id is set to 1
$obj_id = $obj_id ? $obj_id : 1;
$_GET["obj_id"] = $_GET["obj_id"] ? $_GET["obj_id"] : 1;

$obj = getObject($obj_id);

//  Type = usrf => Verzweige nach content_user.php
if($obj["type"] == 'usrf')
{
	header("Location: content_user.php?obj_id=$obj_id&parent=$parent");
	exit();
}
// Type = rolf => Verzweige nach content_role.php
if($obj["type"] == 'rolf')
{
	header("Location: content_role.php?obj_id=$obj_id&parent=$parent");
	exit();
}
// Type = objf => Verzweige nach content_type.php
if($obj["type"] == 'objf')
{
	header("Location: content_type.php?obj_id=$obj_id&parent=$parent");
	exit();
}
// Type = adm => Verzweige nach content_adm.php
if($obj["type"] == 'adm')
{
	header("Location: content_adm.php?obj_id=$obj_id&parent=$parent");
	exit();
}
//  Type = type => Verzweige nach content_type.php
if($obj["type"] == 'type')
{
	header("Location: content_operations.php?obj_id=$obj_id&parent=$parent");
	exit();
}

// Template-Engine anschmeissen
$tplContent = new Template("content_main.html",true,true);

// create tree object: if $pos is not set use root id
$tree =& new Tree($obj_id,1,1);

// was a command submitted?
if (isset($_POST["cmd"]))
{
	$methode = $_POST["cmd"]."Object"; 
	include_once ("classes/class.Admin.php");
	$obj2 = new Admin($ilias);
	$obj2->$methode();
}

// show paste-button if something was cut or copied
if (!empty($clipboard))
{
	$tplContent->touchBlock("btn_paste");
}

// display path
$path = $tree->showPath($tree->getPathFull(),"content.php");
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
		if(!$rbacsystem->checkAccess("visible",$val["id"],$val["parent"]))
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

	// Show only objects with permission 'create'
	$objects = TUtil::getModules($ilias->typedefinition[$type]);
	foreach($objects as $key => $object)
	{
		if($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$key))
		{
			$createable[$key] = $object;
		}
	}
	if(count($createable))
	{
		$opts = TUtil::formSelect(12,"type",$createable);
		$tplContent->setCurrentBlock("type");
		$tplContent->setVariable("SELECT_OBJTYPE",$opts);
		$tplContent->setVariable("OBJ_ID",$obj_id);
		$tplContent->setVariable("TPOS",$parent);
		$tplContent->parseCurrentBlock("opt_type","type",true);
	}
}

$tplContent->setVariable("OBJ_ID",$obj_id);
$tplContent->setVariable("TPOS",$parent);

if($_SESSION["Error_Message"])
{
	$tplContent->setCurrentBlock("sys_message");
	$tplContent->setVariable("ERROR_MESSAGE",$_SESSION["Error_Message"]);
	$tplContent->parseCurrentBlock();
}

include_once "include/ilias_footer.inc";
?>