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


require_once "./include/inc.header.php";
require_once "./classes/class.ilSearch.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.search.html");

sendInfo("Attention: Search function doesn't work in this release.");

if ($_POST["search"] != "")
{
	$mySearch = new ilSearch();
	$mySearch->setArea($_POST["area"]);
	$mySearch->setText($_POST["searchtext"]);
	$mySearch->setOptions($_POST["options"]);

	$tpl->setVariable("SEARCHTEXT", $_POST["searchtext"]);

	//perform search
	if ($mySearch->execute() == true)
	{
		$tpl->setCurrentBlock("message");

		if ($mySearch->hits == 1)
			$msg = "1 ".$lng->txt("hit");
		else
			$msg = $mySearch->hits." ".$lng->txt("hits");
		
		$tpl->setVariable("MSG", $msg);
		$tpl->parseCurrentBlock();

		foreach ($mySearch->result as $row)
		{
		 	$i++;
			$tpl->setCurrentBlock("resultrow");
			$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
			$tpl->setVariable("LINK", $row["link"]);
			$tpl->setVariable("TEXT", $row["text"]);
			$tpl->parseCurrentBlock();
		}
		$tpl->touchBlock("result");
	}
	else
	{
		$tpl->setCurrentBlock("message");
		$tpl->setVariable("MSG", $lng->txt("msg_nothing_found"));
		$tpl->parseCurrentBlock();
	}
}

//fill out select box with search options
$tpl->setCurrentBlock("searcharea");
$tpl->setVariable("SELVALUE", "le");
$tpl->setVariable("SELOPTION", $lng->txt("los"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("searcharea");
$tpl->setVariable("SELVALUE", "usr");
$tpl->setVariable("SELOPTION", $lng->txt("users"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("searcharea");
$tpl->setVariable("SELVALUE", "grp");
$tpl->setVariable("SELOPTION", $lng->txt("groups"));
$tpl->parseCurrentBlock();

$tpl->setVariable("TXT_SEARCH", $lng->txt("search"));

$tpl->setVariable("TXT_SEARCH_IN", $lng->txt("search_in"));
$tpl->setVariable("TXT_KEYWORDS",$lng->txt("keywords"));
$tpl->setVariable("TXT_PHRASE", $lng->txt("phrase"));

$tpl->setVariable("TXT_SEARCH", $lng->txt("search"));

$tpl->show();
?>