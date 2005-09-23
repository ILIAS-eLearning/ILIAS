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

echo "deprecated. use ilias.php?baseClass=ilPersonalDesktopGUI&ampjumpToBookmarks"; exit;

/**
* personal bookmark administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
require_once "./include/inc.header.php";

//
// main
//

// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
	$ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

// determine post or get command

if ($_GET["cmd"] == "post")
{
	if (!empty($_POST["cmd"]))
	{
		$cmd = key($_POST["cmd"]);
	}
	
}
else
{
	$cmd = $_GET["cmd"];
}
if(empty($cmd))
{
	$cmd = "view";
}
$type = (empty($_POST["type"])) ? $_GET["type"] : $_POST["type"];
if(!empty($type) && ($cmd != "delete"))
{
	$cmd.= $objDefinition->getClassName($type);
}


// call method of BookmarkAdministrationGUI class
require_once "./classes/class.ilBookmarkAdministrationGUI.php";
$bookmarkAdminGUI = new ilBookmarkAdministrationGUI($_GET["bmf_id"]);
$bookmarkAdminGUI->$cmd();

$tpl->show();

?>
