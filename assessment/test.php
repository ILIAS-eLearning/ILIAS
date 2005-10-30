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
* assessment test script used to call the test objects
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package assessment
*/
define("ILIAS_MODULE", "assessment");
chdir("..");
include_once "./include/inc.header.php";
include_once "./assessment/classes/class.ilObjTestGUI.php";

unset($id);

//determine call mode for object classes
//TODO: don't use same var $id for both
if ($_GET["obj_id"] > 0)
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
	$ilias->raiseError("No valid ID given! Action aborted", $ilias->error_obj->MESSAGE);
}

switch ($_GET["cmd"]) 
{
	case "run":
		$prepare_output = false;
		break;
	default:
		$prepare_output = true;
		break;
}
if (strcmp($ilCtrl->getCmdClass(), "iltestoutputgui") == 0) $prepare_output = false;
$ilCtrl->setTargetScript("test.php");
$ilCtrl->getCallStructure("ilobjtestgui");
$tst_gui =& new ilObjTestGUI("", $_GET["ref_id"], true, $prepare_output);
$ilCtrl->forwardCommand($tst_gui);

$tpl->show();
?>
