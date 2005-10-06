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
* questionpool script used to call the questionpool objects
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package assessment
*/

define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";
require_once "./assessment/classes/class.ilObjQuestionPoolGUI.php";

// for security
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
	$ilias->raiseError("No valid ID given! Action aborted", $this->ilias->error_obj->MESSAGE);
}

if ((strcmp($_GET["cmd"], "question") == 0) or ($_POST["cmd"]["create"]) or ($_GET["create"])) 
{
	$prepare_output = false;
} 
else 
{
	$prepare_output = true;
}
//$obj = new $class_constr($data, $id, $call_by_reference, $prepare_output);

//echo "ref_id:".$_GET["ref_id"].":";
if ($_GET["obj_id"] < 1) unset($_GET["obj_id"]);
$ilCtrl->setTargetScript("questionpool.php");
$ilCtrl->getCallStructure("ilobjquestionpoolgui");
$qp_gui =& new ilObjQuestionPoolGUI("", $_GET["ref_id"], true, $prepare_output);
$ilCtrl->forwardCommand($qp_gui);


//$obj->$method();
$tpl->show();

?>
