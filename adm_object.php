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
* adm_object
* main script for administration console
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "include/inc.header.php";

// for security
unset($id);

$ilCtrl->setTargetScript("adm_object.php");

//determine call mode for object classes
//TODO: don't use same var $id for both
if ($_GET["obj_id"] != "")
{
	$call_by_reference = false;
	$id = $_GET["obj_id"];
}
else
{
	$call_by_reference = true;
	$id = $_GET["ref_id"];
}

// exit if no valid ID was given
if (!isset($_GET["ref_id"]))
{
	$ilias->raiseError("No valid ID given! Action aborted",$this->ilias->error_obj->MESSAGE);
}

if (!isset($_GET["type"]))
{
	if ($call_by_reference)
	{
		$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
	}
	else
	{
		$obj = $ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
	}

	$_GET["type"] = $obj->getType();
}

// determine command
if ($_GET["cmd"] == "gateway" or $_GET["cmd"] == "post")
{
	// surpress warning if POST is not set
	@$cmd = key($_POST["cmd"]);
}
else
{
	$cmd = $_GET["cmd"];
}

if (empty($cmd)) // if no cmd is given default to first property
{
	$cmd = $_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
}

if ($_GET["cmd"] == "post")
{
	$cmd = key($_POST["cmd"]);
	unset($_GET["cmd"]);
}

// determine object type
if ($_POST["new_type"] && (($cmd == "create") || ($cmd == "import") || ($cmd == "save")))
{
	$obj_type = $_POST["new_type"];
}
elseif ($_GET["new_type"])
{
	$obj_type = $_GET["new_type"];
}
else
{
	$obj_type = $_GET["type"];
}

// call gui object method
$method = $cmd."Object";
$class_name = $objDefinition->getClassName($obj_type);
$module = $objDefinition->getModule($obj_type);
$module_dir = ($module == "")
	? ""
	: $module."/";

$class_constr = "ilObj".$class_name."GUI";
require_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
$ilCtrl->getCallStructure(strtolower("ilObj".$class_name."GUI"));
//echo $class_constr.":".$method;
$obj = new $class_constr($data, $id, $call_by_reference);
$obj->$method();
$tpl->show();
?>
