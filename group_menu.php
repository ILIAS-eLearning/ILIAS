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
require_once "classes/class.ilExplorer.php";
require_once "classes/class.ilGroupExplorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new ilGroupExplorer("group_content.php");

//new ilGroupExplorer("group_content.php");


if ($_GET["expand"] == "")
{
	$expanded = "1";
}
else
	$expanded = $_GET["expand"];
	
$exp->setExpand($expanded);
//filter object types
$exp->addFilter("root");
$exp->addFilter("cat");
$exp->addFilter("grp");
$exp->setFiltered(true);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("EXPLORER",$output);
//$tpl->setVariable("ACTION", "group_menu.php?expand=".$_GET["expand"]);
$tpl->parseCurrentBlock();

$tpl->show();
?>
