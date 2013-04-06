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
	protected $type_white_list = array();
	protected $type_black_list = array();
	
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
	function setOrderField($a_val)
	{
		$this->order_field = $a_val;
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
	 * Get childs of node
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_node_id)
	{
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
	 * @param
	 * @return
	 */
	function getNodeId($a_node)
	{
		return $a_node["child"];
	}

	/**
	 * Get root node
	 *
	 * @param
	 * @return
	 */
	function getRootNode()
	{
		$root_id = $this->getTree()->readRootId();
		return $this->getTree()->getNodeData($root_id);
	}
	
}

?>
