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
* lessons
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./classes/class.ilExplorer.php";
require_once "./classes/class.ilTableGUI.php";

// TODO: this function is common and belongs to class util!
/**
* builds a path string to show the context
* you may leave startnode blank. root node of tree is used instead
* @param	integer	endnode_id
* @param	integer	startnode_id
* @return	string	path
* @access	public
*/
function getContextPath($a_endnode_id, $a_startnode_id = 0)
{
	global $tree;		

	$path = "";		
	
	$tmpPath = $tree->getPathFull($a_endnode_id, $a_startnode_id);		

	// count -1, to exclude the forum itself
	for ($i = 0; $i < (count($tmpPath) - 1); $i++)
	{
		if ($path != "")
		{
			$path .= " > ";
		}

		$path .= $tmpPath[$i]["title"];
	}

	return $path;
}

$tpl->addBlockFile("CONTENT", "content", "tpl.lo_overview.html");
// add everywhere wegen sparkassen skin
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
// display infopanel if something happened
infoPanel();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE",  $lng->txt("lo_available"));
//$tpl->parseCurrentBlock();			// this line produces an empty <h1></h1>, alex 16.2.03

// set offset & limit
$offset = intval($_GET["offset"]);
$limit = intval($_GET["limit"]);

if ($limit == 0)
{
	$limit = 10;	// TODO: move to user settings 
}

// set default sort column
if (empty($_GET["sort_by"]))
{
	$_GET["sort_by"] = "title";
}

if (!isset($_SESSION["viewmode"]))
{
	$_SESSION["viewmode"] = "flat";
}

// display different buttons depending on viewmod
if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","lo.php?viewmode=tree");
	$tpl->setVariable("BTN_TXT", $lng->txt("treeview"));
	$tpl->parseCurrentBlock();
}
else
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","lo.php?viewmode=flat");
	$tpl->setVariable("BTN_TARGET","target=\"_parent\"");
	$tpl->setVariable("BTN_TXT", $lng->txt("flatview"));
	$tpl->parseCurrentBlock();
}

// display different content depending on viewmode
switch ($_SESSION["viewmode"])
{
	case "flat":
		$lr_arr = ilUtil::getObjectsByOperations('le','visible');
		$lr_arr = ilUtil::getObjectsByOperations('crs','visible');
		break;
		
	case "tree":
		//go through valid objects and filter out the lessons only
		$lr_arr = array();
		$objects = $tree->getChilds($_GET["ref_id"],"title");

		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				if ($object["type"] == "le" && $rbacsystem->checkAccess('visible',$object["child"]))
				{
					$lr_arr[$key] = $object;
				}
			}
		}
		break;
}

$maxcount = count($lr_arr);		// for numinfo in table footer
require_once "./include/inc.sort.php";
$lr_arr = sortArray($lr_arr,$_GET["sort_by"],$_GET["sort_order"]);
$lr_arr = array_slice($lr_arr,$offset,$limit);
	
// load template for table
$tpl->addBlockfile("LO_TABLE", "lo_table", "tpl.table.html");
// load template for table content data
$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lo_tbl_rows.html");

$lr_num = count($lr_arr);

// render table content data
if ($lr_num > 0)
{
	// counter for rowcolor change
	$num = 0;

	foreach ($lr_arr as $lr_data)
	{
		$tpl->setCurrentBlock("tbl_content");

		// change row color
		$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
		$num++;

		$obj_link = "lo_view.php?lm_id=".$lr_data["ref_id"];
		$obj_icon = "icon_".$lr_data["type"]."_b.gif";

		$tpl->setVariable("TITLE", $lr_data["title"]);
		$tpl->setVariable("LO_LINK", $obj_link);

		if ($lr_data["type"] == "le")		// Test
		{
			$tpl->setVariable("EDIT_LINK","content/lm_edit.php?lm_id=".$lr_data["obj_id"]);
			$tpl->setVariable("TXT_EDIT", "(".$lng->txt("edit").")");
			$tpl->setVariable("VIEW_LINK","content/lm_presentation.php?lm_id=".$lr_data["obj_id"]);
			$tpl->setVariable("TXT_VIEW", "(".$lng->txt("view").")");
		}

		$tpl->setVariable("IMG", $obj_icon);
		$tpl->setVariable("ALT_IMG", $lng->txt("obj_".$lr_data["type"]));
		$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
		$tpl->setVariable("STATUS", "N/A");
		$tpl->setVariable("LAST_VISIT", "N/A");
		$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
		$tpl->setVariable("CONTEXTPATH", getContextPath($lr_data["ref_id"]));
		$tpl->parseCurrentBlock();
	}
}
else
{
	$tpl->setCurrentBlock("no_content");
	$tpl->setVariable("TXT_MSG_NO_CONTENT",$lng->txt("lo_no_content"));
	$tpl->parseCurrentBlock("no_content");
}

// create table
$tbl = new ilTableGUI();

// title & header columns
$tbl->setTitle($lng->txt("lo_available"),"icon_crs_b.gif",$lng->txt("lo_available"));
$tbl->setHelp("tbl_help.php","icon_help.gif",$lng->txt("help"));
$tbl->setHeaderNames(array($lng->txt("title"),$lng->txt("description"),$lng->txt("status"),$lng->txt("last_visit"),$lng->txt("last_change"),$lng->txt("context")));
$tbl->setHeaderVars(array("title","description","status","last_visit","last_update","context"));
//$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));

// control
$tbl->setOrderColumn($_GET["sort_by"]);
$tbl->setOrderDirection($_GET["sort_order"]);
$tbl->setLimit($limit);
$tbl->setOffset($offset);
$tbl->setMaxCount($maxcount);

// footer
$tbl->setFooter("tblfooter",$lng->txt("previous"),$lng->txt("next"));
//$tbl->disable("content");
//$tbl->disable("footer");

// render table
$tbl->render();

$tpl->show();
?>
