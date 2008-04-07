<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once("./Services/Form/classes/class.ilHierarchyFormGUI.php");

/**
* This class represents a hierarchical form. These forms are used for
* quick editing, where each node is represented by it's title.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilChapterHierarchyFormGUI extends ilHierarchyFormGUI
{
	/**
	* Constructor
	*
	* @param
	*/
	function __construct()
	{
		parent::__construct();
		$this->setCheckboxName("id");
	}
	
	/**
	* Get menu items
	*/
	function getMenuItems($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs)
	{
		$cmds = array();
		
		if (!$a_first_child)		// drop area of node
		{
			if ($a_node["type"] == "pg" || ($a_node["type"] == "st" && count($a_childs) == 0))
			{
				if ($a_node["type"] == "st")
				{
					$cmds[] = array("text" => "insert Page", "cmd" => "insertPage", "multi" => 10,
						"as_subitem" => true);
				}
				else
				{
					$cmds[] = array("text" => "insert Page", "cmd" => "insertPage", "multi" => 10);
				}
			}
			if ($a_node["type"] != "pg")
			{
				$cmds[] = array("text" => "insert Subchapter", "cmd" => "insertSubchapter", "multi" => 10);
			}
			
			if (($a_next_sibling["type"] != "pg" && ($a_depth == 0 || $a_next_sibling["type"] == "st"))
				|| $a_node["type"] == "st")
			{
				$cmds[] = array("text" => "insert Chapter", "cmd" => "insertChapter", "multi" => 10);
			}
		}
		else						// drop area before first child of node
		{
			if ($a_node["type"] == "st")
			{
				$cmds[] = array("text" => "insert Page", "cmd" => "insertPage", "multi" => 10);
			}
			if ($a_childs[0]["type"] != "pg")
			{
				$cmds[] = array("text" => "insert Chapter", "cmd" => "insertChapter", "multi" => 10);
			}
		}

		return $cmds;
	}

	/**
	* Which nodes allow child nodes?
	*/
	function nodeAllowsChilds($a_node)
	{
		if ($a_node["type"] == "pg")
		{
			return false;
		}
		return true;
	}

	/**
	* Makes nodes drag and drop content and targets.
	*
	* @param	object	$a_node		node array
	*/
	function manageDragAndDrop($a_node, $a_depth, $a_first_child_drop_area = false, $a_next_sibling = null, $a_childs = null)
	{
		global $lng;
		
		$this->makeDragContent($a_node["node_id"], "grp_".$a_node["type"]);
		
		if (!$a_first_child_drop_area)
		{
			if ($a_node["type"] == "pg" || ($a_node["type"] == "st" && count($a_childs) == 0))
			{
				if ($a_node["type"] == "st")
				{
					$this->makeDragTarget($a_node["node_id"], "grp_pg", $a_first_child_drop_area,
						true, "");
				}
				else
				{
					$this->makeDragTarget($a_node["node_id"], "grp_pg", $a_first_child_drop_area,
						false, "");
				}
			}
			
			if ($a_node["type"] != "pg")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
					true, $lng->txt("cont_insert_as_subchapter"));
			}
			
			if (($a_next_sibling["type"] != "pg" && ($a_depth == 0 || $a_next_sibling["type"] == "st"))
				|| $a_node["type"] == "st")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
					false, $lng->txt("cont_insert_as_chapter"));
			}
		}
		else
		{
			if ($a_node["type"] == "st")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_pg", $a_first_child_drop_area,
					true);
			}
			if ($a_childs[0]["type"] != "pg")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
					true);
			}
		}
	}

}
