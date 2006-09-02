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
 * startpage for ilias
 * this file decides if a frameset is used or not.
 * Frames set definition is done in 'tpl.start.html'
 * 
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-core
 * @version $Id$
*/
//require_once "./include/inc.header.php";

require_once "./include/inc.header.php";
ilUtil::redirect("index.php");

exit;
//include("index.php");
//exit;


global $ilBench, $ilCtrl;

if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID || !empty($_GET["ref_id"]))
{
	if (empty($_GET["ref_id"]))
	{
		$_GET["ref_id"] = ROOT_FOLDER_ID;
	}
	$ilCtrl->initBaseClass("");
	$ilCtrl->setCmd("frameset");
	$start_script = "repository.php";
}
else
{
	$ilCtrl->initBaseClass("ilPersonalDesktopGUI");
	$start_script = "ilias.php";
}

include($start_script);


?>
