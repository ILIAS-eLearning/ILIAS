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

//determine call mode for object classes
//TODO: don't use same var $id for both
if (isset($_GET["obj_id"]))
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
if ($_GET["type"]!="root"  ) 
{
if ($_GET["type"]!="cat"  ) 
{
	$cmd = key($_POST["cmd"]);
}
}

// determine command
//if (($cmd = $_GET["cmd"]) == "gateway")
//{
//	$cmd = key($_POST["cmd"]);
//}
//if (empty($cmd)) // if no cmd is given default to first property
//{
	/*//$cmd = $_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
	$cmd = $_GET["cmd"] = "content";*/
//}

// determine object type
if ($_POST["new_type"] && (($cmd == "create") || ($cmd == "import")))
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

//echo "e";
//var_dump($_GET);
//var_dump($_POST);

// call gui object method
//$method = $cmd."Object";
$class_name = $objDefinition->getClassName($obj_type);

if ($_GET["type"]=="root" or $_GET["type"]=="cat")
{
	$class_constr = "ilObj".$class_name."GUI";
	require_once("./classes/class.ilObj".$class_name."GUI.php");
}
else
{
	$class_constr = "il".$class_name."GUI";
	require_once("./classes/class.il".$class_name."GUI.php");
}
//echo $class_constr.":".$method;
//$obj = new $class_constr($data, $id, $call_by_reference);
//$obj->$method();

//$tpl->show();

require_once "./include/inc.header.php";
require_once "./classes/class.ilGroupGUI.php";
      // echo $_GET["expand"];
//var_dump($_GET);echo "sssssss";var_dump($_POST);
//echo "ref_id".$_GET["ref_id"]."grp_id".$_GET["grp_id"];


//var_dump($_GET); echo "fffff";var_dump($_POST);
//$grp_gui =& new ilGroupGUI($data, $id, $call_by_reference);
if ($_POST["new_type"]!="fold")
{
$grp_gui =& new ilGroupGUI($data, $id, $call_by_reference);
}
//echo $class_constr;


if ($_POST["new_type"]=="fold")
{
	$obj = new $class_constr($data, $id, $call_by_reference);
	$method= $cmd."Object";
	$obj->$method();
}


?>

