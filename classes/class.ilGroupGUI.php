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

require_once("class.ilObjGroupGUI.php");

/**
* Class ilGroupGUI 
*
* GUI class for ilLearningModule
*
* @author Martin Rus <mrus@smail.uni-koeln.de>
* @version $Id$
*
* @package group
*/
class ilGroupGUI extends ilObjGroupGUI
{
	var $g_obj;
	var $g_tree;
	var $tpl;
	var $lng;
	var $objDefinition;
	var $tree;
	var $rbacsystem;
	/**
	* Constructor
	* @access	public
	*/
	function ilGroupGUI($a_ref_id = 0)
	{
		global $tpl, $lng, $tree, $rbacsystem;
		
		
		if($a_ref_id != 0)
		{
		parent::ilObjGroupGUI("", $a_ref_id, true, false);
		}
		
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->tree =& $tree;
		$this->rbacsystem = $rbacsystem;
		
		
		$cmd = $_GET["cmd"];
		if($cmd == "")
		{
			$cmd = "view";
		}

		$this->$cmd();
		
		var_dump($_GET);
	}
	/**
	* calls current view mode (tree frame or list)
	*/
	function view()
	{
		if (isset($_GET["viewmode"]))
		{
			$_SESSION["viewmode"] = $_GET["viewmode"];
		}
	    
		// tree frame
		if ($_SESSION["viewmode"] == "tree")
		{
			$this->tpl = new ilTemplate("tpl.group.html", false, false);
			$this->tpl->show();
		}
		else	// list
		{
			$this->displayList();
		}
	}
	
	function getContextPath($a_endnode_id, $a_startnode_id = 0)
{
	global $tree;		

	$path = "";		
	
	$tmpPath = $this->tree->getPathFull($a_endnode_id, $a_startnode_id);		

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
	
	/**
	* display list of courses and learning modules
	*/
	function displayList()
	{ 
	
	global  $tree, $rbacsystem;
	
	require_once "./include/inc.header.php";
	require_once "./classes/class.ilExplorer.php";
	require_once "./classes/class.ilTableGUI.php";




	$this->tpl->addBlockFile("CONTENT", "content", "tpl.groups_overview.html");
	$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");	
	infoPanel();


//$this->tpl->setCurrentBlock("content");
$this->tpl->setVariable("TXT_PAGEHEADLINE",  $this->lng->txt("groups_overview"));

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


if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$this->tpl->setCurrentBlock("btn_cell");
	$this->tpl->setVariable("BTN_LINK","group.php?viewmode=tree");
	$this->tpl->setVariable("BTN_TXT", $this->lng->txt("treeview"));
	$this->tpl->parseCurrentBlock();
}
else
{
	$this->tpl->setCurrentBlock("btn_cell");
	$this->tpl->setVariable("BTN_LINK","group.php?viewmode=flat");
	$this->tpl->setVariable("BTN_TARGET","target=\"_parent\"");
	$this->tpl->setVariable("BTN_TXT", $this->lng->txt("flatview"));
	$this->tpl->parseCurrentBlock();
}

$this->tpl->setCurrentBlock("btn_cell");
$this->tpl->setVariable("BTN_LINK","group_new.php?parent_ref_id=".$_GET["ref_id"]);
$this->tpl->setVariable("BTN_TXT", $this->lng->txt("group_new"));
$this->tpl->parseCurrentBlock();


// display different content depending on viewmode
switch ($_SESSION["viewmode"])
{
	case "flat":
		$cont_arr = ilUtil::getObjectsByOperations('grp','visible');
		break;
		
	case "tree":
		//go through valid objects and filter out the lessons only
		$cont_arr = array();
		$objects = $tree->getChilds($_GET["ref_id"],"title");
		
		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				if ($object["type"] == "grp" && $rbacsystem->checkAccess('visible',$object["child"]))
				{
					$cont_arr[$key] = $object;
				}
			}
		}
		break;
}

$maxcount = count($cont_arr);	

require_once "./include/inc.sort.php";
$cont_arr = sortArray($cont_arr,$_GET["sort_by"],$_GET["sort_order"]);
$cont_arr = array_slice($cont_arr,$offset,$limit);
	

// load template for table
$this->tpl->addBlockfile("GROUP_TABLE", "group_table", "tpl.table.html");
// load template for table content data
$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");
$cont_num = count($cont_arr);


// render table content data
if ($cont_num > 0)
{ 
	// counter for rowcolor change
	$num = 0;
//	var_dump ($cont_arr);
	foreach ($cont_arr as $cont_data)
	{
		$this->tpl->setCurrentBlock("tbl_content");
		$newuser = new ilObjUser($cont_data["owner"]);
		// change row color
		$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
		$num++;

		$obj_link = "group.php?ref_id=".$cont_data["ref_id"];
		$obj_icon = "icon_".$cont_data["type"]."_b.gif";
		$this->tpl->setVariable("TITLE", $cont_data["title"]);
		$this->tpl->setVariable("LINK", $obj_link);
		$this->tpl->setVariable("LINK_TARGET", "_parent");
		$this->tpl->setVariable("IMG", $obj_icon);
		$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
		$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
		$this->tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
		$this->tpl->setVariable("LAST_VISIT", "N/A");
		//$this->tpl->setVariable("ROLE_IN_GROUP", "keine Rolle zugewiesen");
		$this->tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);//ilFormat::formatDate($cont_data["last_update"])
		$this->tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
		$this->tpl->parseCurrentBlock();		
		 
	}
}
else
{
	$this->tpl->setCurrentBlock("no_content");
	$this->tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
	$this->tpl->parseCurrentBlock("no_content");
}

// create table
$tbl = new ilTableGUI();

// title & header columns
$tbl->setTitle($this->lng->txt("lo_available"),"icon_crs_b.gif",$this->lng->txt("lo_available"));
$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("owner"),$this->lng->txt("last_visit"),$this->lng->txt("last_change"),$this->lng->txt("context")));
$tbl->setHeaderVars(array("title","description","owner","last_visit","last_change","context"));
$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));




// control
$tbl->setOrderColumn($_GET["sort_by"]);
$tbl->setOrderDirection($_GET["sort_order"]);
$tbl->setLimit($limit);
$tbl->setOffset($offset);
$tbl->setMaxCount($maxcount);

// footer
$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
$tbl->disable("content");
$tbl->disable("footer");

// render table
$tbl->render();

$this->tpl->show();

	
	}



	function explorer()
	{ 
		
		
		require_once "include/inc.header.php";
		require_once "classes/class.ilExplorer.php";
		require_once "classes/class.ilGroupExplorer.php";

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		$exp = new ilGroupExplorer("group.php?cmd=displayList");
		var_dump($_GET["expand"]);
		if ($_GET["expand"] == "")
		{
			$expanded = "1";
		}
		else
		{
			$expanded = $_GET["expand"];
		}
	
		$exp->setExpand($expanded);
	
		//filter object types
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->setFiltered(true);

		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("EXPLORER",$output);
		//$this->tpl->setVariable("ACTION", "group_menu.php?expand=".$_GET["expand"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->show();
	
}	

}

?>
