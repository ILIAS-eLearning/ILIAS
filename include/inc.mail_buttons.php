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
* buttons for mail module
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

$lng->loadLanguageModule("mail");
$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

$script_name = basename($_SERVER["SCRIPT_FILENAME"]);

$file_name = basename($_SERVER["SCRIPT_NAME"]);

// FOLDER
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE", ($script_name == "mail.php" || $script_name == "mail_read.php")
	? "tabactive"
	: "tabinactive");
$tpl->setVariable("TAB_LINK", "mail.php?mobj_id=$_GET[mobj_id]&type=new");
$tpl->setVariable("TAB_TEXT", $lng->txt("fold"));
$tpl->parseCurrentBlock();


// COMPOSE
$tpl->setCurrentBlock("tab");
$tpl->setVAriable("TAB_TYPE", ($script_name == "mail_new.php" || $script_name == "mail_attachment.php"
	|| $script_name == "mail_search.php")
	? "tabactive"
	: "tabinactive");
$tpl->setVariable("TAB_LINK", "mail_new.php?mobj_id=$_GET[mobj_id]&type=new");
$tpl->setVariable("TAB_TEXT", $lng->txt("compose"));
$tpl->parseCurrentBlock();

// ADDRESSBOOK
$tpl->setCurrentBlock("tab");
$tpl->setVAriable("TAB_TYPE",$script_name == "mail_addressbook.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK", "mail_addressbook.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("TAB_TEXT", $lng->txt("mail_addressbook"));
$tpl->parseCurrentBlock();

// OPTIONS
$tpl->setCurrentBlock("tab");
$tpl->setVAriable("TAB_TYPE",$script_name == "mail_options.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK", "mail_options.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("TAB_TEXT", $lng->txt("options"));
$tpl->parseCurrentBlock();

// FLATVIEW <-> TREEVIEW
if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$tpl->setCurrentBlock("tree_mode");
	$tpl->setVariable("LINK_MODE","mail_frameset.php?viewmode=tree&amp;mobj_id=".$_GET["mobj_id"]);
	$tpl->setVariable("IMG_TREE", ilUtil::getImagePath("ic_treeview.gif"));
	$tpl->parseCurrentBlock();
}
else
{
	$tpl->setCurrentBlock("tree_mode");
	$tpl->setVariable("LINK_MODE","mail_frameset.php?viewmode=flat&amp;mobj_id=".$_GET["mobj_id"]);
	$tpl->setVariable("IMG_TREE", ilUtil::getImagePath("ic_flatview.gif"));
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("tree_icons");
$tpl->parseCurrentBlock();
?>