<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesUIComponent
 */
abstract class ilTreeExplorerGUI extends ilExplorerBaseGUI
{
	protected $tree = null;
	protected $order_field = "";
	protected $order_field_numeric = false;
	protected $type_white_list = array();
	protected $type_black_list = array();
	protected $childs = array();			// preloaded childs
	protected $preloaded = false;
	protected $preload_childs = false;
	protected $root_node_data = null;
	protected $all_childs = array();
	
	/**
	 * Constructor
	 */
	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree)
	{
		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);
		$this->tree = $a_tree;
	}
	
	/**
	 * Get tree
	 *
	 * @return object tree object
	 */
	function getTree()
	{
		return $this->tree;
	}
	
	/**
	 * Set order field
	 *
	 * @param string $a_val order field key	
	 */
	function setOrderField($a_val, $a_numeric = false)
	{
		$this->order_field = $a_val;
		$this->order_field_numeric = $a_numeric;
	}
	
	/**
	 * Get order field
	 *
	 * @return string order field key
	 */
	function getOrderField()
	{
		return $this->order_field;
	}
	
	/**
	 * Set type white list
	 *
	 * @param array $a_val array of strings of node types that should be retrieved	
	 */
	function setTypeWhiteList($a_val)
	{
		$this->type_white_list = $a_val;
	}
	
	/**
	 * Get type white list
	 *
	 * @return array array of strings of node types that should be retrieved
	 */
	function getTypeWhiteList()
	{
		return $this->type_white_list;
	}
	
	/**
	 * Set type black list
	 *
	 * @param array $a_val array of strings of node types that should be filtered out	
	 */
	function setTypeBlackList($a_val)
	{
		$this->type_black_list = $a_val;
	}
	
	/**
	 * Get type black list
	 *
	 * @return array array of strings of node types that should be filtered out
	 */
	function getTypeBlackList()
	{
		return $this->type_black_list;
	}

	/**
	 * Set preload childs
	 *
	 * @param boolean $a_val preload childs
	 */
	function setPreloadChilds($a_val)
	{
		$this->preload_childs = $a_val;
	}

	/**
	 * Get preload childs
	 *
	 * @return boolean preload childs
	 */
	function getPreloadChilds()
	{
		return $this->preload_childs;
	}

	/**
	 * Preload childs
	 */
	protected function preloadChilds()
	{
		$subtree = $this->tree->getSubTree($this->getRootNode());
		foreach ($subtree as $s)
		{
			$wl = $this->getTypeWhiteList();
			if (is_array($wl) && count($wl) > 0 && !in_array($s["type"], $wl))
			{
				continue;
			}
			$bl = $this->getTypeBlackList();
			if (is_array($bl) && count($bl) > 0 && in_array($s["type"], $bl))
			{
				continue;
			}
			$this->childs[$s["parent"]][] = $s;
			$this->all_childs[$s["child"]] = $s;
		}

		if ($this->order_field != "")
		{
			foreach ($this->childs as $k => $childs)
			{
				$this->childs[$k] = ilUtil::sortArray($childs, $this->order_field, "asc", $this->order_field_numeric);
			}
		}

		// sort childs and store prev/next reference
		if ($this->order_field == "")
		{
			$this->all_childs =
				ilUtil::sortArray($this->all_childs, "lft", "asc", true, true);
			$prev = false;
			foreach ($this->all_childs as $k => $c)
			{
				if ($prev)
				{
					$this->all_childs[$prev]["next_node_id"] = $k;
				}
				$this->all_childs[$k]["prev_node_id"] = $prev;
				$this->all_childs[$k]["next_node_id"] = false;
				$prev = $k;
			}
		}

		$this->preloaded = true;
	}



	/**
	 * Get successor node (currently only(!) based on lft/rgt tree values)
	 *
	 * @param integer $a_node_id node id
	 * @param string $a_type node type
	 * @return mixed node id or false
	 */
	function getSuccessorNode($a_node_id, $a_type = "")
	{
		if ($this->order_field != "")
		{
			die("ilTreeExplorerGUI::getSuccessorNode not implemented for order field ".$this->order_field);
		}

		if ($this->preloaded)
		{
			$next_id = $a_node_id;
			while (($next_id = $this->all_childs[$next_id]["next_node_id"]) && $a_type != "" &&
				$this->all_childs[$next_id]["type"] != $a_type);
			if ($next_id)
			{
				return $this->all_childs[$next_id];
			}
			return false;
		}
		return $this->getTree()->fetchSuccessorNode($a_node_id, $a_type);
	}



	/**
	 * Get childs of node
	 *
	 * @param int $a_parent_node_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_node_id)
	{
		if ($this->preloaded)
		{
			if (is_array($this->childs[$a_parent_node_id]))
			{
				return $this->childs[$a_parent_node_id];
			}
			return array();
		}

		$wl = $this->getTypeWhiteList();
		if (is_array($wl) && count($wl) > 0)
		{
			$childs = $this->tree->getChildsByTypeFilter($a_parent_node_id, $wl, $this->getOrderField());
		}
		else
		{
			$childs = $this->tree->getChilds($a_parent_node_id, $this->getOrderField());
		}
		
		// apply black list filter
		$bl = $this->getTypeBlackList();
		if (is_array($bl) && count($bl) > 0)
		{
			$bl_childs = array();
			foreach($childs as $k => $c)
			{
				if (!in_array($c["type"], $bl))
				{
					$bl_childs[$k] = $c;
				}
			}
			return $bl_childs; 
		}
		
		return $childs;
	}
	
	/**
	 * Get id for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string id
	 */
	function getNodeId($a_node)
	{
		return $a_node["child"];
	}

	/**
	 * Get node icon alt attribute
	 *
	 * @param mixed $a_node node object/array
	 * @return string image alt attribute
	 */
	function getNodeIconAlt($a_node)
	{
		global $lng;
		
		return $lng->txt("icon")." ".$lng->txt("obj_".$a_node["type"]);
	}

	/**
	 * Get root node
	 *
	 * @return mixed node object/array
	 */
	function getRootNode()
	{
		if (isset($this->root_node_data))
		{
			return $this->root_node_data;
		}
		$root_id = $this->getTree()->readRootId();
		$this->root_node_data =  $this->getTree()->getNodeData($root_id);
		return $this->root_node_data;
	}
	
	/**
	 * Set node path to be opened
	 *
	 * @param string $a_id node id
	 */
	function setPathOpen($a_id)
	{
		$path = $this->getTree()->getPathId($a_id);
		foreach ($path as $id)
		{
			$this->setNodeOpen($id);
		}
	}

	/**
	 * Get HTML
	 *
	 * @return string html
	 */
	function getHTML()
	{
		if ($this->getPreloadChilds())
		{
			$this->preloadChilds();
		}
		return parent::getHTML();
	}


}

?>
