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
* obj_location_menu.php
* main tree to select location of new objects
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* @package ilias-core
*/
require_once "include/inc.header.php";
require_once "classes/class.ilExplorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new ilExplorer("obj_location_content.php");

$exp->setExpand($_GET["expand"]);
$exp->setParamsGet(array("new_type"=>$_GET["new_type"],"cmd"=>"create"));

$exp->addFilter("root");
$exp->addFilter("cat");

if ($_GET["new_type"] != "grp")
{
	$exp->addFilter("grp");
}

if ($_GET["new_type"] != "crs" and $_GET["new_type"] != "grp")
{
	$exp->addFilter("crs");
}

$exp->setFiltered(true);

$exp->setOutput(0);

$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_EXPLORER_HEADER", $lng->txt("choose_location"));
$tpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "obj_location_menu.php?expand=".$_GET["expand"]);
$tpl->parseCurrentBlock();

$tpl->show();
?>