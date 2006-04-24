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


require_once "include/inc.header.php";
require_once "classes/class.ilMailExplorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
$tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));

$exp = new ilMailExplorer("mail.php",$_SESSION["AccountId"]);
$exp->setTargetGet("mobj_id");

if ($_GET["mexpand"] == "")
{
	$mtree = new ilTree($_SESSION["AccountId"]);
	$mtree->setTableNames('mail_tree','mail_obj_data');
	$expanded = $mtree->readRootId();
}
else
	$expanded = $_GET["mexpand"];
	
$exp->setExpand($expanded);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_EXPLORER_HEADER", $lng->txt("mail_folders"));
$tpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "mail_menu.php?mexpand=".$_GET["mexpand"]);
$tpl->parseCurrentBlock();

$tpl->show(false);

?>
