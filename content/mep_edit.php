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
* edit media pools
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");
require_once "./include/inc.header.php";
$lng->loadLanguageModule("content");

// check write permission
if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]) &&
	!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

// editor GUI class does the rest
require_once "./content/classes/class.ilObjMediaPoolGUI.php";
$ilCtrl->setTargetScript("mep_edit.php");

$ilCtrl->getCallStructure("ilobjmediapoolgui");
$media_pool_gui =& new ilObjMediaPoolGUI("", $_GET["ref_id"],true, false);
//$media_pool_gui->executeCommand();
$ilCtrl->forwardCommand($media_pool_gui);

?>
