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
* main menu
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*/

if (is_dir($tpl->tplPath."/".$ilias->account->prefs["style"]))
{
	$image_dir = "/".$ilias->account->prefs["style"];
}
else
{
	$image_dir = "";
}

if ($rbacsystem->checkAccess("visible", ROOT_FOLDER_ID))
{
	$tpl->setCurrentBlock("userisadmin");
	$tpl->setVariable("IMAGE_DIR", $image_dir);
	$tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
	$tpl->parseCurrentBlock();
}
// limit access only to authors
if ($rbacsystem->checkAccess("write", ROOT_FOLDER_ID))
{
	$tpl->setCurrentBlock("userisauthor");
	$tpl->setVariable("IMAGE_DIR", $image_dir);
	$tpl->setVariable("TXT_EDITOR", $lng->txt("editor"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("navigation");
$tpl->setVariable("IMAGE_DIR", $image_dir);
$tpl->setVariable("TXT_PERSONAL_DESKTOP", $lng->txt("personal_desktop"));
$tpl->setVariable("TXT_LO_OVERVIEW", $lng->txt("lo_overview"));
$tpl->setVariable("TXT_BOOKMARKS", $lng->txt("bookmarks"));
$tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
$tpl->setVariable("TXT_LITERATURE", $lng->txt("literature"));
$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
$tpl->setVariable("TXT_FORUMS", $lng->txt("forums"));
$tpl->setVariable("TXT_GROUPS", $lng->txt("groups"));
$tpl->setVariable("TXT_HELP", $lng->txt("help"));
$tpl->setVariable("TXT_FEEDBACK", $lng->txt("feedback"));
$tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
$tpl->parseCurrentBlock();
?>
