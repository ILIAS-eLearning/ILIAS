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
define("ILIAS_MODULE", "survey");
chdir("..");
require_once "./include/inc.header.php";
require_once "./survey/classes/class.ilObjSurveyQuestionPoolGUI.php";

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


$ilCtrl->setTargetScript("questionpool.php");
$ilCtrl->getCallStructure("ilobjsurveyquestionpoolgui");
$qp_gui =& new ilObjSurveyQuestionPoolGUI("", $_GET["ref_id"], true, true);
$ilCtrl->forwardCommand($qp_gui);
$tpl->show();
?>

