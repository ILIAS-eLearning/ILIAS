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
* button bar in main screen
* adapted from ilias 2
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-layout
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "navigation", "tpl.main_buttons.html");
$tpl->setVariable("IMG_DESK", ilUtil::getImagePath("navbar/desk.gif", false));
$tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
$tpl->setVariable("IMG_COURSE", ilUtil::getImagePath("navbar/course.gif", false));
$tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("navbar/mail.gif", false));
$tpl->setVariable("IMG_FORUMS", ilUtil::getImagePath("navbar/newsgr.gif", false));
$tpl->setVariable("IMG_SEARCH", ilUtil::getImagePath("navbar/search.gif", false));
$tpl->setVariable("IMG_LITERAT", ilUtil::getImagePath("navbar/literat.gif", false));
$tpl->setVariable("IMG_GROUPS", ilUtil::getImagePath("navbar/groups.gif", false));
$tpl->setVariable("IMG_ADMIN", ilUtil::getImagePath("navbar/admin.gif", false));
$tpl->setVariable("IMG_HELP", ilUtil::getImagePath("navbar/help.gif", false));
$tpl->setVariable("IMG_FEEDB", ilUtil::getImagePath("navbar/feedb.gif", false));
$tpl->setVariable("IMG_LOGOUT", ilUtil::getImagePath("navbar/logout.gif", false));
$tpl->setVariable("IMG_ILIAS", ilUtil::getImagePath("navbar/ilias.gif", false));
$tpl->setVariable("JS_BUTTONS", ilUtil::getJSPath("buttons.js"));
include("./include/inc.mainmenu.php");

$tpl->show();

?>
