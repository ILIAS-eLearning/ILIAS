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
* displays a user profile
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @package ilias
*/

require_once "./include/inc.header.php";
require_once './Services/User/classes/class.ilObjUserGUI.php';

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_profile_view.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

// set locator
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("who_is_online"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php?cmd=whoisdetail");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("userdata"));
$tpl->setVariable("LINK_ITEM", "profile.php?user=".$_GET["user"]);
$tpl->setVariable("LINK_TARGET","target=\"bottom\"");
$tpl->parseCurrentBlock();

$_GET["obj_id"] = $_GET["user"];
$user = new ilObjUserGUI("",$_GET["user"], false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("personal_desktop"));

// catch feedback message
ilUtil::sendInfo();
// display infopanel if something happened
ilUtil::infoPanel();

// display tabs
include "./include/inc.personaldesktop_buttons.php";

$user->insertPublicProfile("USR_PROFILE","usr_profile");

$tpl->show();
?>