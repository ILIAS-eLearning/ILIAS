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
* buttons for personaldesktop
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias
*/

$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

$script_name = basename($_SERVER["SCRIPT_FILENAME"]);

$command = $_GET["cmd"] ? $_GET["cmd"] : "";

if (ereg("whois",$command) or $script_name == "profile.php")
{
	$who_is_online = true;
}


// personal desktop home
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "usr_personaldesktop.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","usr_personaldesktop.php");
$tpl->setVariable("TAB_TEXT",$lng->txt("overview"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

// user profile
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "usr_profile.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","usr_profile.php");
$tpl->setVariable("TAB_TEXT",$lng->txt("personal_profile"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
{
/*
	// user calendar
	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE", "tabinactive");
	$tpl->setVariable("TAB_LINK","cal_month_overview.php");
	$tpl->setVariable("TAB_TEXT",$lng->txt("calendar"));
	$tpl->setVariable("TAB_TARGET","bottom");
	$tpl->parseCurrentBlock();

	// user agreement
	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE",$script_name == "usr_agreement.php" ? "tabactive" : "tabinactive");
	$tpl->setVariable("TAB_LINK","usr_agreement.php");
	$tpl->setVariable("TAB_TEXT",$lng->txt("usr_agreement"));
	$tpl->setVariable("TAB_TARGET","bottom");
	$tpl->parseCurrentBlock();
*/

	// user bookmarks
	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE",$script_name == "usr_bookmarks.php" ? "tabactive" : "tabinactive");
	$tpl->setVariable("TAB_LINK","usr_bookmarks.php?cmd=frameset");
	$tpl->setVariable("TAB_TEXT",$lng->txt("bookmarks"));
	$tpl->setVariable("TAB_TARGET","bottom");
	$tpl->parseCurrentBlock();
}
?>
