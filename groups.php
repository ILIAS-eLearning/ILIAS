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

$grp_sys[] = array("name" => "Administrator",
				"desc" => "System Administrators",
				"owner" => "System Administrator [root]"
			);

			
$groups = array();

//go through valid objects and filter out the lessons only
$objects = $tree->getChilds(ROOT_FOLDER_ID,"title");

if (count($objects) > 0)
{
	foreach ($objects as $key => $object)
	{
		if (($object["type"] == "cat" || $object["type"] == "grp") && $rbacsystem->checkAccess('visible',$object["child"]))
		{
			$groups[$key] = $object;
		}
	}
}

//TODO: maybe move the code above to this method
//$groups = $ilias->account->getGroups();

$tpl->addBlockFile("CONTENT", "content", "tpl.groups.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

sendInfo("Not available in this release.");

/*
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","group_new.php");
$tpl->setVariable("BTN_TXT", $lng->txt("new_group"));
$tpl->parseCurrentBlock();
$tpl->touchBlock("btn_row");

$i=0;

foreach ($grp_sys as $row)
{
	$i++;
	$tpl->setCurrentBlock("group_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("GRP_NAME", $row["name"]);
	$tpl->setVariable("GRP_DESC", $row["desc"]);
	$tpl->setVariable("GRP_OWNER", $row["owner"]);
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("group");
$tpl->setVariable("TXT_GRP_TITLE", $lng->txt("system_groups"));
$tpl->setVariable("TXT_NAME", $lng->txt("name"));
$tpl->setVariable("TXT_DESC", $lng->txt("description"));
$tpl->setVariable("TXT_OWNER", $lng->txt("owner"));

$tpl->parseCurrentBlock("group");

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_GROUPS", $lng->txt("groups"));
$tpl->parseCurrentBlock();
*/
$tpl->show();
?>