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
 * group objects mainpage
 * 
 * this file decides if a frameset is used or not
 * 
 * @author Martin Rus <pmrus@smail.uni-koeln.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";

//look if there is a file tpl.group.html
$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.group.html"; 

if (isset($_GET["viewmode"]))
{
	$_SESSION["viewmode"] = $_GET["viewmode"];
}


if (file_exists($startfilename) and ($_SESSION["viewmode"] == "tree"))
{
	$tpl = new ilTemplate("tpl.group.html", false, false);
	$tpl->show();
}
else
{
	header("location: group_content.php?expand=1");
}

?>
