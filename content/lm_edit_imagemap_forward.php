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
require_once "./content/classes/Pages/class.ilMediaObjectGUI.php";

// recover parameter
ilMediaObjectGUI::_recoverParameters();

if ($_GET["coords"] != "")
{
	$_GET["coords"] .= ",";
}
$pos = strpos($QUERY_STRING, "?");
if ($pos > 0)
{
	$_GET["coords"] .= substr($QUERY_STRING, $pos + 1, strlen($QUERY_STRING) - $pos);
}
else
{
	$_GET["coords"] .= $QUERY_STRING;
}


// call lm_edit script
if ($_SESSION["il_map_edit_mode"] != "edit_shape")
{
	header("Location: lm_edit.php?ref_id=".$_GET["ref_id"].
		"&obj_id=".$_GET["obj_id"]."&mode=page_edit&hier_id=".$_GET["hier_id"].
		"&cmd=addArea&coords=".$_GET["coords"]);
}
else
{
	header("Location: lm_edit.php?ref_id=".$_GET["ref_id"].
		"&obj_id=".$_GET["obj_id"]."&mode=page_edit&hier_id=".$_GET["hier_id"].
		"&cmd=setShape&coords=".$_GET["coords"]);
}

?>
