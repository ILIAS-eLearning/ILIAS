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
require_once "./classes/class.ilTableGUI.php";

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

function getURLbyType($cont_data)
{
	switch ($cont_data["type"])
	{ 
		
  		case "frm":
			$URL = "forums_threads_liste.php?ref_id=".$cont_data["ref_id"];
		break;	
		
		case "crs":
			$URL = "lo_content.php?ref_id=".$cont_data["ref_id"];
		break;
		
		case "le":
			$URL = "content/lm_presentation.php?lm_id=".$cont_data["ref_id"];
		break;
	}

return $URL;
}


$tpl->addBlockFile("CONTENT", "content", "tpl.group_detail.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
infoPanel();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE",  $lng->txt("group_details"));

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

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","group_members.php");
$tpl->setVariable("BTN_TXT", $lng->txt("group_members"));
$tpl->parseCurrentBlock();

$cont_arr = array();
		
$objects = $tree->getChilds($_GET["grp_id"],"title");
		
		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				if ($rbacsystem->checkAccess('visible',$object["child"]))
				{
					$cont_arr[$key] = $object;
				}
			}
		}
		
$maxcount = count($cont_arr);


// load template for table
$tpl->addBlockfile("GROUP_DETAILS_TABLE", "group_table", "tpl.table.html");
// load template for table content data
$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows_checkbox.html");
$cont_num = count($cont_arr);

// render table content data
if ($cont_num > 0)
{ 
	// counter for rowcolor change
	$num = 0;

	foreach ($cont_arr as $cont_data)
	{	
		$tpl->setCurrentBlock("tbl_content");
		$newuser = new ilObjUser($cont_data["owner"]);
		// change row color
		$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
		$num++;

		$obj_link = getURLbyType($cont_data);
		
		$obj_icon = "icon_".$cont_data["type"]."_b.gif";
		$tpl->setVariable("CHECKBOX", ilUtil::formCheckBox(0,"",""));
		$tpl->setVariable("TITLE", $cont_data["title"]);
		$tpl->setVariable("LO_LINK", $obj_link);
		
		$tpl->setVariable("IMG", $obj_icon);
		$tpl->setVariable("ALT_IMG", $lng->txt("obj_".$cont_data["type"]));
		$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
		$tpl->setVariable("OWNER", $newuser->getFullName());
		$tpl->setVariable("LAST_VISIT", "N/A");
		$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cont_data["last_update"]));				
		$tpl->setVariable("CONTEXTPATH", getContextPath($cont_data["ref_id"]));
		$tpl->parseCurrentBlock();
		
		 
	}
}
else
{
	$tpl->setCurrentBlock("no_content");
	$tpl->setVariable("TXT_MSG_NO_CONTENT",$lng->txt("group_any_objects"));
	$tpl->parseCurrentBlock("no_content");
}

// create table
$tbl = new ilTableGUI();

// title & header columns
//$tbl->setTitle($lng->txt("lo_available"),"icon_crs_b.gif",$lng->txt("lo_available"));
//$tbl->setHelp("tbl_help.php","icon_help.gif",$lng->txt("help"));
$tbl->setHeaderNames(array("",$lng->txt("title"),$lng->txt("description"),$lng->txt("owner"),$lng->txt("last_visit"),$lng->txt("last_change"),$lng->txt("context")));
$tbl->setHeaderVars(array("checkbox","title","description","status","last_visit","last_change","context"));
$tbl->setColumnWidth(array("3%","7%","7%","15%","15%","6%","22%"));

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
