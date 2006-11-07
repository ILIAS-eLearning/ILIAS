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
* image map editing forward script for learning module editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");
require_once "./include/inc.header.php";
require_once "./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php";

// recover parameter
ilObjMediaObjectGUI::_recoverParameters();

if ($_SESSION["il_map_edit_coords"] != "")
{
	$_SESSION["il_map_edit_coords"] .= ",";
}
$pos = strpos($QUERY_STRING, "?");
if ($pos > 0)
{
	$_SESSION["il_map_edit_coords"] .= substr($QUERY_STRING, $pos + 1, strlen($QUERY_STRING) - $pos);
}
else
{
	$_SESSION["il_map_edit_coords"] .= $QUERY_STRING;
}


// call lm_edit script
ilUtil::redirect($_SESSION["il_map_edit_target_script"]);

?>
