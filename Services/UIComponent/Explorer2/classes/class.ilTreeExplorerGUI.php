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
	 * Get childs of node
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_node_id)
	{
		return $this->tree->getChilds($a_parent_node_id, $this->getOrderField());
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
