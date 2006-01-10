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
require_once "classes/class.ilForumExplorer.php";


$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new ilForumExplorer("./forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]",$_GET["thr_pk"],(int) $_GET['ref_id']);
$exp->setTargetGet("pos_pk");

if ($_GET["fexpand"] == "")
{
	$forum = new ilForum();
	$tmp_array = $forum->getFirstPostNode($_GET["thr_pk"]);
	$expanded = $tmp_array["id"];
}
else
	$expanded = $_GET["fexpand"];
	
$exp->setExpand($expanded);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_EXPLORER_HEADER", $lng->txt("forums_posts"));
$tpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "forums_menu.php?fexpand=".$_GET["fexpand"]."&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
$tpl->parseCurrentBlock();

$tpl->show(false);
//$tpl->show(true);
?>
