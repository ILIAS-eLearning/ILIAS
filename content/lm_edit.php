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
* learning module editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");
require_once "./include/inc.header.php";

//echo "lm_edit begin:".$_SESSION["il_map_il_target"].":<br>";

$lng->loadLanguageModule("content");

// check write permission
if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

if ($_GET["cmd"] == "popup") 
{
	include_once "./content/classes/Pages/class.ilWysiwygUtil.php";
	$popup = new ilWysiwygUtil();
	$popup->show($_GET["ptype"]);
} 
else 
{
	
	include_once "./content/classes/class.ilLMEditorGUI.php";
	$ilCtrl->setTargetScript("lm_edit.php");
	$ilBench->start("Editor", "getCallStructure");
	$ilCtrl->getCallStructure("illmeditorgui");
	$ilBench->stop("Editor", "getCallStructure");
	
	// editor GUI class does the rest
	$lm_editor_gui =& new ilLMEditorGUI();
	$ilCtrl->forwardCommand($lm_editor_gui);
	//$lm_editor_gui->executeCommand();
	
	//$tpl->show();
	
	//echo "lm_edit end:".$_SESSION["il_map_il_target"].":<br>";
	$ilBench->save();
}

?>
