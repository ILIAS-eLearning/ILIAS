<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("./Services/Form/classes/class.ilFormGUI.php");

/**
* This class represents a hierarchical form. These forms are used for
* quick editing, where each node is represented by it's title.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilHierarchyFormGUI extends ilFormGUI
{
	/**
	* Constructor
	*
	* @param
	*/
	function __construct()
	{
		global $lng, $tpl;
		
		$this->maxdepth = -1;
		$this->multi_commands = array();
		$this->commands = array();
		$this->drag_target[] = array();
		$this->drag_content[] = array();
		$lng->loadLanguageModule("form");
		$this->setCheckboxName("cbox");
		parent::ilFormGUI();
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initDragDrop();
		$tpl->addJavascript("./Services/Form/js/ServiceFormHierarchyForm.js");
	}

	/**
	* Set Id. Currently not possible, due to js handling (ID must always be "hform")
	*
	* @param	string	$a_id	Id
	*/
	function setId($a_id)
	{
		die("ilHierarchyFormGUI does currently not support multiple forms (multiple IDs). ID is always hform.");
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	function getId()
	{
		return "hform";
	}

	/**
	* Set Tree Object.
	*
	* @param	object	$a_tree	Tree Object
	*/
	function setTree($a_tree)
	{
		$this->tree = $a_tree;
	}

	/**
	* Get Tree Object.
	*
	* @return	object	Tree Object
	*/
	function getTree()
	{
		return $this->tree;
	}

	/**
	* Set Current Top Node ID.
	*
	* @param	string	$a_currenttopnodeid	Current Top Node ID
	*/
	function setCurrentTopNodeId($a_currenttopnodeid)
	{
		$this->currenttopnodeid = $a_currenttopnodeid;
	}

	/**
	* Get Current Top Node ID.
	*
	* @return	string	Current Top Node ID
	*/
	function getCurrentTopNodeId()
	{
		return $this->currenttopnodeid;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Checkbox Name.
	*
	* @param	string	$a_checkboxname	Checkbox Name
	*/
	function setCheckboxName($a_checkboxname)
	{
		$this->checkboxname = $a_checkboxname;
	}

	/**
	* Get Checkbox Name.
	*
	* @return	string	Checkbox Name
	*/
	function getCheckboxName()
	{
		return $this->checkboxname;
	}

	/**
	* Set Maximum Depth.
	*
	* @param	int	$a_maxdepth	Maximum Depth
	*/
	function setMaxDepth($a_maxdepth)
	{
		$this->maxdepth = $a_maxdepth;
	}

	/**
	* Get Maximum Depth.
	*
	* @return	int	Maximum Depth
	*/
	function getMaxDepth()
	{
		return $this->maxdepth;
	}

	/**
	* Makes a nodes (following droparea) a drag target
	*
	* @param	string	$a_id		node ID
	* @param	string	$a_group	drag and drop group
	*/
	function makeDragTarget($a_id, $a_group, $a_first_child_drop_area = false, $a_as_subitem = false, $a_diss_text = "")
	{
		if ($a_first_child_drop_area == true)		// first child drop areas only insert as subitems
		{
			$a_as_subitem = true;
		}
		
		if ($a_id != "")
		{
			if ($a_first_child_drop_area)
			{
				$a_id.= "fc";
			}
			
			$this->drag_target[] = array("id" => $a_id, "group" => $a_group);
			$this->diss_menues[$a_id][$a_group][] = array("subitem" => $a_as_subitem, "text" => $a_diss_text);
		}
	}
	
	/**
	* Makes a node a drag content
	*
	* @param	string	$a_id		node ID
	* @param	string	$a_group	drag and drop group
	*/
	function makeDragContent($a_id, $a_group)
	{
		if ($a_id != "")
		{
			$this->drag_content[] = array("id" => $a_id, "group" => $a_group);
		}
	}

	/**
	* Add a multi command (for selection of items)
	*
	* @param	string	$a_txt	command text
	* @param	string	$a_cmd	command
	*/
	function addMultiCommand($a_txt, $a_cmd)
	{
		$this->multi_commands[] = array("text" => $a_txt, "cmd" => $a_cmd);
	}

	/**
	* Add a command
	*
	* @param	string	$a_txt	command text
	* @param	string	$a_cmd	command
	*/
	function addCommand($a_txt, $a_cmd)
	{
		$this->commands[] = array("text" => $a_txt, "cmd" => $a_cmd);
	}

	/**
	* Get all childs of current node. Standard implementation uses
	* tree object.
	*/
	function getChilds($a_node_id = false)
	{
		if ($a_node_id == false)
		{
			$a_node_id = $this->getCurrentTopNodeId();
		}
		
		$tree_childs = $this->getTree()->getChilds($a_node_id);
		$childs = array();
		foreach($tree_childs as $tree_child)
		{
			$childs[] = array("node_id" => $tree_child["child"],
				"title" => $tree_child["title"],
				"type" => $tree_child["type"]);
		}
		
		return $childs;
	}
	
	/**
	* Get Form Content
	*/
	function getContent()
	{
		global $lng;
		
		$ttpl = new ilTemplate("tpl.hierarchy_form.html", true, true, "Services/Form");
		$top_node_data = $this->getTree()->getNodeData($this->getCurrentTopNodeId());
		$top_node = array("node_id" => $top_node_data["child"],
				"title" => $top_node_data["title"],
				"type" => $top_node_data["type"]);
		
		$childs = null;
		$nodes_html = $this->getLevelHTML($top_node, 0, $childs);
		
		// commands
		if (count($this->multi_commands) > 0 || count($this->commands) > 0)
		{
			foreach($this->commands as $cmd)
			{
				$ttpl->setCurrentBlock("cmd");
				$ttpl->setVariable("CMD", $cmd["cmd"]);
				$ttpl->setVariable("CMD_TXT", $cmd["text"]);
				$ttpl->parseCurrentBlock();
			}

			foreach($this->multi_commands as $cmd)
			{
				$ttpl->setCurrentBlock("multi_cmd");
				$ttpl->setVariable("MULTI_CMD", $cmd["cmd"]);
				$ttpl->setVariable("MULTI_CMD_TXT", $cmd["text"]);
				$ttpl->parseCurrentBlock();
			}
			
			$ttpl->setCurrentBlock("commands");
			$ttpl->setVariable("MCMD_ALT", $lng->txt("commands"));
			$ttpl->setVariable("MCMD_IMG", ilUtil::getImagePath("arrow_downright.gif"));
			$ttpl->parseCurrentBlock();
		}
		
		// drag and drop initialisation
		foreach($this->drag_target as $drag_target)
		{
			$ttpl->setCurrentBlock("dragtarget");
			$ttpl->setVariable("EL_ID", $drag_target["id"]);
			$ttpl->setVariable("GROUP", $drag_target["group"]);
			$ttpl->parseCurrentBlock();
		}
		foreach($this->drag_content as $drag_content)
		{
			$ttpl->setCurrentBlock("dragcontent");
			$ttpl->setVariable("EL_ID", $drag_content["id"]);
			$ttpl->setVariable("GROUP", $drag_content["group"]);
			$ttpl->parseCurrentBlock();
		}
		
		// disambiguation menues and "insert as first child" flags
		if (is_array($this->diss_menues))
		{
			foreach($this->diss_menues as $node_id => $d_menu)
			{
				foreach($d_menu as $group => $menu)
				{
					if (count($menu) > 1)
					{
						foreach($menu as $menu_item)
						{
							$ttpl->setCurrentBlock("dmenu_cmd");
							$ttpl->setVariable("SUBITEM", (int) $menu_item["subitem"]);
							$ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
							$ttpl->parseCurrentBlock();
						}
						
						$ttpl->setCurrentBlock("disambiguation_menu");
						$ttpl->setVariable("DNODE_ID", $node_id);
						$ttpl->setVariable("GRP", $group);
						$ttpl->parseCurrentBlock();
					}
					else if (count($menu) == 1)
					{
						// set first child flag
						$ttpl->setCurrentBlock("as_subitem_flag");
						$ttpl->setVariable("SI_NODE_ID", $node_id);
						$ttpl->setVariable("SI_GRP", $group);
						$ttpl->setVariable("SI_SI", (int) $menu[0]["subitem"]);
						$ttpl->parseCurrentBlock();
						
					}
				}
			}
		}
		$this->diss_menues[$a_id][$a_group][] = array("type" => $a_type, "text" => $a_diss_text);
		
		
		// nodes
		$ttpl->setVariable("NODES", $nodes_html);
		
		// title
//echo "<br>".htmlentities($this->getTitle())." --- ".htmlentities(ilUtil::prepareFormOutput($this->getTitle()));
		$ttpl->setVariable("TITLE", $this->getTitle());
		
		return $ttpl->get();
	}
	
	/**
	* Get Form HTML
	*/
	function getLevelHTML($a_par_node, $a_depth, &$a_childs)
	{
		if ($this->getMaxDepth() > -1 && $this->getMaxDepth() < $a_depth)
		{
			return "";
		}
		
		$childs = $this->getChilds($a_par_node["node_id"]);
		$a_childs = $childs;
//var_dump($a_par_node);
		$html = "";
		$last_child = null;
		$ttpl = new ilTemplate("tpl.hierarchy_form_nodes.html", true, true, "Services/Form");
		
		// prepended drop area
		if ($this->nodeAllowsChilds($a_par_node) && (count($childs) > 0 || $a_depth == 0))
		{
			$ttpl->setCurrentBlock("drop_area");
			$ttpl->setVariable("DNODE_ID", $a_par_node["node_id"]."fc");		// fc means "first child"
			$ttpl->setVariable("IMG_BLANK", ilUtil::getImagePath("blank.gif"));
			$ttpl->parseCurrentBlock();
	
			$this->manageDragAndDrop($a_par_node, $a_depth, true, null, $childs);

			$menu_items = $this->getMenuItems($a_par_node, $a_depth, true, null, $childs);
//var_dump($menu_items);
			if (count($menu_items) > 0)
			{
				// determine maximum of multi add numbers
				$max = 1;
				foreach($menu_items as $menu_item)
				{
					if ($menu_item["multi"] > $max)
					{
						$max = $menu_item["multi"];
					}
				}
				
				reset($menu_items);
				foreach($menu_items as $menu_item)
				{
					if ($menu_item["multi"] > 1 )
					{
						for($i = 2; $i <= $menu_item["multi"]; $i++)
						{
							$ttpl->setCurrentBlock("multi_add");
							$ttpl->setVariable("MA_NUM", $i);
							$ttpl->setVariable("MENU_CMD", $menu_item["cmd"]);
							$ttpl->setVariable("FC", "1");
							$ttpl->setVariable("CMD_NODE", $a_par_node["node_id"]);
							$ttpl->parseCurrentBlock();
						}
					}
					
					// buffer td for lower multis
					if ($max > $menu_item["multi"])
					{
						$ttpl->setCurrentBlock("multi_buffer");
						$ttpl->setVariable("BUF_SPAN", $max - $menu_item["multi"]);
						$ttpl->parseCurrentBlock();
					}
					$ttpl->setCurrentBlock("menu_cmd");
					$ttpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
					$ttpl->setVariable("MENU_CMD", $menu_item["cmd"]);
					$ttpl->setVariable("CMD_NODE", $a_par_node["node_id"]);
					$ttpl->setVariable("FC", "1");
					$ttpl->parseCurrentBlock();
				}
				$ttpl->setCurrentBlock("drop_area_menu");
				$ttpl->setVariable("MNODE_ID", $a_par_node["node_id"]."fc");
				$ttpl->parseCurrentBlock();
	
				$ttpl->setCurrentBlock("element");
				$ttpl->parseCurrentBlock();
			}
		}
		
		// insert childs
		if (count($childs) > 0)
		{
			for($i = 0; $i < count($childs); $i++)
			{
				$next_sibling = ($i < (count($childs) - 1))
					? $next_sibling = $childs[$i+1]
					: null;
				$this->renderChild($ttpl, $childs[$i], $a_depth, $next_sibling);
				$last_child = $child;
			}
		}

		$html = $ttpl->get();
		unset($ttpl);
		
		return $html;
	}
	
	/**
	* Render a single child (including grandchilds)
	*/
	function renderChild($a_tpl, $a_child, $a_depth, $next_sibling = null)
	{
		// image
		$a_tpl->setCurrentBlock("img");
		$a_tpl->setVariable("IMGPATH", $this->getIcon($a_child));
		$a_tpl->setVariable("IMGALT", $this->getIconAlt($a_child));
		$a_tpl->setVariable("IMG_NODE", $a_child["node_id"]);
		$a_tpl->setVariable("TYPE", $a_child["type"]);
		$a_tpl->parseCurrentBlock();
		
		// checkbox
		$a_tpl->setCurrentBlock("cbox");
		$a_tpl->setVariable("CNODE_ID", $a_child["node_id"]);
		$a_tpl->setVariable("CBOX_NAME", $this->getCheckboxName());
		$a_tpl->parseCurrentBlock();
		
		// title
		$a_tpl->setCurrentBlock("text");
		$a_tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_child["title"]));
		$a_tpl->setVariable("TNODE_ID", $a_child["node_id"]);
		$a_tpl->parseCurrentBlock();
		$grandchilds = null;
		$grandchilds_html = $this->getLevelHTML($a_child, $a_depth + 1, $grandchilds);
		
		// childs
		$a_tpl->setCurrentBlock("list_item");
		$a_tpl->setVariable("CHILDS", $grandchilds_html);
		$a_tpl->parseCurrentBlock();
		
		$a_tpl->setCurrentBlock("element");
		$a_tpl->parseCurrentBlock();
		
		// drop area after child
		$a_tpl->setCurrentBlock("drop_area");
		$a_tpl->setVariable("DNODE_ID", $a_child["node_id"]);
		$a_tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("blank.gif"));
		$a_tpl->parseCurrentBlock();

		// manage drag and drop areas
		$this->manageDragAndDrop($a_child, $a_depth, false, $next_sibling, $grandchilds);
		
		// drop area menu
		$menu_items = $this->getMenuItems($a_child, $a_depth, false, $next_sibling, $grandchilds);
		if (count($menu_items) > 0)
		{
			// determine maximum of multi add numbers
			$max = 1;
			foreach($menu_items as $menu_item)
			{
				if ($menu_item["multi"] > $max)
				{
					$max = $menu_item["multi"];
				}
			}
			
			reset($menu_items);
			foreach($menu_items as $menu_item)
			{
				if ($menu_item["multi"] > 1 )
				{
					for($i = 2; $i <= $menu_item["multi"]; $i++)
					{
						$a_tpl->setCurrentBlock("multi_add");
						$a_tpl->setVariable("MA_NUM", $i);
						$a_tpl->setVariable("MENU_CMD", $menu_item["cmd"]);
						if ($menu_item["as_subitem"])
						{
							$a_tpl->setVariable("FC", "1");
						}
						else
						{
							$a_tpl->setVariable("FC", "0");
						}
						$a_tpl->setVariable("CMD_NODE", $a_child["node_id"]);
						$a_tpl->parseCurrentBlock();
					}
				}
				
				// buffer td for lower multis
				if ($max > $menu_item["multi"])
				{
					$a_tpl->setCurrentBlock("multi_buffer");
					$a_tpl->setVariable("BUF_SPAN", $max - $menu_item["multi"]);
					$a_tpl->parseCurrentBlock();
				}
				
				$a_tpl->setCurrentBlock("menu_cmd");
				$a_tpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
				$a_tpl->setVariable("MENU_CMD", $menu_item["cmd"]);
				if ($menu_item["as_subitem"])
				{
					$a_tpl->setVariable("FC", "1");
				}
				else
				{
					$a_tpl->setVariable("FC", "0");
				}
				$a_tpl->setVariable("CMD_NODE", $a_child["node_id"]);
				$a_tpl->parseCurrentBlock();
			}
			$a_tpl->setCurrentBlock("drop_area_menu");
			$a_tpl->setVariable("MNODE_ID", $a_child["node_id"]);
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("element");
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	* Get icon path for an item.
	*
	* @param	array		item array
	* @return	string		icon path
	*/
	function getIcon($a_item)
	{
		return ilUtil::getImagePath("icon_".$a_item["type"].".gif");
	}
	
	/**
	* Get icon alt text for an item.
	*
	* @param	array		item array
	* @return	string		icon alt text
	*/
	function getIconAlt($a_item)
	{
		global $lng;
		
		return $lng->txt($a_item["type"]);
	}

	/**
	* Get menu items for drop area of node.
	*
	* This function will be most likely overwritten by sub class
	*
	* @param	array	$a_child		node array ("title", "node_id", "type")
	* @param	boolean	$a_first_child	if false, the menu of the drop area
	*									right after the node (same level) is set
	*									if true, the menu of the drop area before
	*									the first child (if nodes are allowed)
	*									of the node is set
	*/
	function getMenuItems($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs = null)
	{
		return array();
	}
	
	/**
	* Checks, whether current nodes allows childs at all.
	* Should be overwritten.
	*/
	function nodeAllowsChilds($a_node)
	{
		return true;
	}
	
	/**
	* Makes nodes drag and drop content and targets.
	* Must be overwritten to support drag and drop.
	*
	* @param	object	$a_node		node array
	*/
	function manageDragAndDrop($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs = null)
	{
		//$this->makeDragTarget($a_node["id"], $a_group);
		//$this->makeDragTarget($a_node["id"], $a_group);
	}

	/**
	* Get multi number of _POST input
	*/
	static function getPostMulti()
	{
		return (int) ($_POST["il_hform_multi"] > 1
			? $_POST["il_hform_multi"]
			: 1);
	}
	
	/**
	* Get node ID of _POST input
	*/
	static function getPostNodeId()
	{
		return $_POST["il_hform_node"];
	}

	/**
	* Should node be inserted as first child of target node (true) or as successor (false)
	*/
	static function getPostFirstChild()
	{
		return (((int) $_POST["il_hform_fc"]) == 1);
	}
	
}
