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
* groups
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tplbtn = new ilTemplate("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","groups.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("back"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new ilTemplate("tpl.group_new.html", false, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());

//$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("new_group"));

$tpl->setVariable("TXT_GROUPNAME", $lng->txt("groupname"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
$tpl->setVariable("TXT_ACCESS", $lng->txt("access"));
$tpl->setVariable("TXT_GROUP_SCOPE", $lng->txt("groupscope"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>
