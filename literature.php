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
* literature
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.literature.html");

sendInfo("Attention: Functions to handle literature bookmarks ist not implemented yet.");

$lit = $ilias->account->getLiterature();

foreach ($lit as $row)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("DESC", $row["desc"]);
	$tpl->setVariable("URL", $row["url"]);
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_LITERATURE",  $lng->txt("literature_bookmarks"));
$tpl->setVariable("TXT_DESCRIPTION",  $lng->txt("description"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->show();
?>