<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* obj_location_content.php
* 
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "./include/inc.header.php";

if (isset($_GET["from"]))
{
	$_SESSION["obj_location_back"] = $_GET["from"];
}

// determine command
if (($cmd = $_GET["cmd"]) == "gateway")
{
	// TODO: temp. workaround until cmd is passed by post for lm & dbk
	if ($_GET["new_type"] == "lm" or $_GET["new_type"] == "dbk")
	{
		$cmd = "save";
	}
	else
	{
		// surpress warning if POST is not set
		@$cmd = key($_POST["cmd"]);
	}
}

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.new_obj_content.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

// set locator 
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");

if (isset($_GET["ref_id"]) and ($_GET["ref_id"] != 1))
{
	$path = "";		

	$tmpPath = $tree->getPathFull($_GET["ref_id"]);		
	// count -1, to exclude the forum itself
	for ($i = 0; $i < (count($tmpPath)); $i++)
	{
		if ($path != "")
		{
			$path .= " > ";
		}
			$path .= $tmpPath[$i]["title"];						
	}
	
	$tpl->setVariable("TARGET_LOCATOR",$lng->txt("at_location").": ".$path);
}

switch ($_GET["new_type"])
{
	case "frm":
		$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
		break;
	
	case "grp":
		$tpl->setVariable("ITEM", $lng->txt("groups_overview"));
		break;
		
	case "crs":
	case "lm":
		$tpl->setVariable("ITEM", $lng->txt("lo_available"));
		break;
}

$tpl->setVariable("LINK_ITEM", $_SESSION["obj_location_back"]);
$tpl->setVariable("LINK_TARGET","target=\"bottom\"");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt($_GET["new_type"]."_add"));
$tpl->setVariable("LINK_ITEM", "obj_location_new.php?new_type=".$_GET["new_type"]);
$tpl->setVariable("LINK_TARGET","target=\"bottom\"");
$tpl->parseCurrentBlock();

// catch feedback message
sendInfo();
// display infopanel if something happened
infoPanel();

//add template for buttons
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("content");

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt($_GET["new_type"]."_add"));
$tpl->setVariable("TXT_INSTRUCTION", $lng->txt("txt_add_object_instruction1")." ".$lng->txt($_GET["new_type"]."_a")." ".$lng->txt("txt_add_object_instruction2"));

if (isset($_GET["ref_id"]) && ($_GET["ref_id"] != 1))
{
	if (!$rbacsystem->checkAccess("create",$_GET["ref_id"],"frm"))
	{
		sendInfo($lng->txt("msg_no_perm_create_object1")." ".$lng->txt($_GET["new_type"]."_a")." ".$lng->txt("msg_no_perm_create_object2"));
	}
	else
	{		
		$id = $_GET["ref_id"];
		$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

		$_GET["type"] = $obj->getType();
		
		$obj_type = $_GET["new_type"];
		$class_name = $objDefinition->getClassName($obj_type);
		$module = $objDefinition->getModule($obj_type);
		
		$module_dir = ($module == "") ? "" : $module."/";
		$class_constr = "ilObj".$class_name."GUI";
		include_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
		$obj = new $class_constr($data, $id, true, false);

		$method = $cmd."Object";

		// set return Locations
		switch ($_GET["new_type"])
		{
			case "frm":
				$obj->setReturnLocation("save","forums.php");
				$obj->setReturnLocation("cancel","forums.php");
				break;
	
			case "grp":
				$obj->setReturnLocation("save","group.php");
				$obj->setReturnLocation("cancel","group.php");
				break;
		
			case "crs":
			case "lm":
				$obj->setReturnLocation("save","lo_list.php");
				$obj->setReturnLocation("cancel","lo_list.php");
				break;
		}

		$obj->setFormAction("save","obj_location_content.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$obj_type);
		$obj->setTargetFrame("save","bottom");

		$obj->$method();
	}
}

// output
$tpl->show();
?>
