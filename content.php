<?php
include_once "include/ilias_header.inc";

$obj = getObject($_GET["obj_id"]);

//  Type = usrf => Verzweige nach content_user.php
if($obj["type"] == 'usrf')
{
	header("Location: content_user.php?obj_id=$obj_id&parent=$parent&order=$_GET[order]&direction=$_GET[direction]");
	exit();
}
// Type = rolf => Verzweige nach content_role.php
if($obj["type"] == 'rolf')
{
	header("Location: content_role.php?obj_id=$obj_id&parent=$parent&order=$_GET[order]&direction=$_GET[direction]");
	exit();
}
// Type = objf => Verzweige nach content_type.php
if($obj["type"] == 'objf')
{
	header("Location: content_type.php?obj_id=$obj_id&parent=$parent&order=$_GET[order]&direction=$_GET[direction]");
	exit();
}
// Type = adm => Verzweige nach content_adm.php
if($obj["type"] == 'adm')
{
	header("Location: content_adm.php?obj_id=$obj_id&parent=$parent&order=$_GET[order]&direction=$_GET[direction]");
	exit();
}
//  Type = type => Verzweige nach content_type.php
if($obj["type"] == 'type')
{
	header("Location: content_operations.php?obj_id=$obj_id&parent=$parent&order=$_GET[order]&direction=$_GET[direction]");
	exit();
}
// Template-Engine anschmeissen
$tplContent = new Template("content_main.html",true,true);

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
	$tplContent->touchBlock("btn_clear");
}

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

// display path
//$path = $tree->showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);
//$tplContent->setVariable("OBJ_SELF",substr(strrchr($REQUEST_URI, "/"), 1));
$tplContent->setVariable("OBJ_SELF","content.php?parent=$parent&obj_id=$obj_id");

$tplContent->setCurrentBlock("row",true);

$rbacsystem = new RbacSystemH($ilias->db);
if ($tree->getChilds($_GET["obj_id"],$_GET["order"],$_GET["direction"]))
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
		$tplContent->setVariable("OBJ_DESC",$val["desc"]);
		$tplContent->setVariable("OBJ_LAST_UPDATE",$val["last_update"]);
		$tplContent->setVariable("IMG_TYPE","icon_".$val["type"]."_b.gif");
		$tplContent->setVariable("ALT_IMG_TYPE",$val["type"]);
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

if($_GET["message"])
{
	$tplContent->setCurrentBlock("sys_message");
	$tplContent->setVariable("ERROR_MESSAGE",$_GET["message"]);
	$tplContent->parseCurrentBlock();
}

//testing

$flat = $tree->calculateFlatTree(1);

$flat_tree = "<table>\n<tr>\n<th>\nno.</th>\n<th>\nname</th>\n<th>\nnode_id</th>\n<th>\nsucc</th>\n<th>\ndepth</th>\n<th>\nbrother</th>\n<th>\nlft</th>\n<th>\nrgt</th>\n</tr>\n";

foreach ($flat as $key => $node)
{
	$flat_tree .= "<tr>\n<td>\n".$key."</td>\n<td>\n".$node["title"]."</td>\n<td>\n".$node["child"]."</td>\n<td>\n".$node["successor"]."</td>\n<td>\n".$node["depth"]."</td>\n<td>\n".$node["brother"]."</td>\n<td>\n".$node["lft"]."</td>\n<td>\n".$node["rgt"]."</td>\n</tr>\n";
}

$flat_tree .= "</table>\n";

	$tplContent->setVariable("TESTING",$flat_tree);
	
include_once "include/ilias_footer.inc";
?>