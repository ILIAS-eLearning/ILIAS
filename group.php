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
* group.php
* main script for group management
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/

//var_dump($_POST)."#".var_dump($_GET);
require_once "include/inc.header.php";
require_once "./classes/class.ilGroupGUI.php";

// for security
unset($id);

$call_by_reference = true;
$id = $_GET["ref_id"];

// exit if no valid ID was given
if (!isset($_GET["ref_id"]))
{
	$ilias->raiseError("No valid ID given! Action aborted",$this->ilias->error_obj->MESSAGE);
}

if (!isset($_GET["type"]))
{
	$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

	$_GET["type"] = $obj->getType();
}

if (isset($_POST["cmd"]) or isset($_GET["new_type"]) )
{
		if ($_GET["gateway"] == "true")
		{
			$grp_gui =& new ilGroupGUI($data, $id, $call_by_reference);
			exit();
		}
		else
		{
			if (isset($_POST["cmd"]))
			{
				$cmd = key($_POST["cmd"]);
			}
			else
			{
				$cmd = $_GET["cmd"];
			}
			if (isset($_POST["new_type"]))
			{
				$obj_type = $_POST["new_type"];
			}
			else
			{
				$obj_type = $_GET["new_type"];
				
			}
//			echo "typ".$obj_type;
//			echo "cmd".$cmd;
			$class_name = $objDefinition->getClassName($obj_type);
			$module = $objDefinition->getModule($obj_type);
//			echo ("modul:  ".$module);
//			echo ("objtype: ".$obj_type);

$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

switch ($obj->getType())
{
	case "crs":
	case "frm":
	case "lm":
	case "slm":
	case "glo":
		// nix
		break;

	case "fold":
	case "file":
	//echo "hier_group.php";exit;
		if ($obj_type != "fold")
		$_GET["ref_id"] = $obj->getGroupId();
		break;
}

			$module_dir = ($module == "")
				? ""
				: $module."/";
			$class_constr = "ilObj".$class_name."GUI";
			//echo "class kons ".$class_constr." module_dir ".$module_dir;exit;
			require_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
			$obj = new $class_constr($data, $id, $call_by_reference,false);
			$method= $cmd."Object";
			$obj->setReturnLocation("save","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
			$obj->$method();
		}
}
else
{
	$grp_gui =& new ilGroupGUI($data, $id, $call_by_reference);
	exit();
}
?>

