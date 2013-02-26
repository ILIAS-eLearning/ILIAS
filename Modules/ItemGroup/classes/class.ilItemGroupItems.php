<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Item group items.
 *
 * This class is used to store the materials (items) that are assigned
 * to an item group. Main table used is item_group_item
 *
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 * 
 */
class ilItemGroupItems
{
	var $ilDB;
	var $tree;
	var $lng;

	var $item_group_id = 0;
	var $item_group_ref_id = 0;
	var $items = array();

	/**
	 * Constructor
	 *
	 * @param int $a_item_group_ref_id ref id of item group
	 */
	function ilItemGroupItems($a_item_group_ref_id = 0)
	{
		global $ilDB, $lng, $tree, $objDefinition;

		$this->db  = $ilDB;
		$this->lng = $lng;
		$this->tree = $tree;
		$this->obj_def = $objDefinition;

		$this->setItemGroupRefId((int) $a_item_group_ref_id);
		if ($this->getItemGroupRefId() > 0)
		{
			$this->setItemGroupId((int) ilObject::_lookupObjId($a_item_group_ref_id));
		}

		if ($this->getItemGroupId() > 0)
		{
			$this->read();
		}
	}

	/**
	 * Set item group id
	 *
	 * @param int $a_val item group id	
	 */
	function setItemGroupId($a_val)
	{
		$this->item_group_id = $a_val;
	}
	
	/**
	 * Get item group id
	 *
	 * @return int item group id
	 */
	function getItemGroupId()
	{
		return $this->item_group_id;
	}
	
	/**
	 * Set item group ref id
	 *
	 * @param int $a_val item group ref id	
	 */
	function setItemGroupRefId($a_val)
	{
		$this->item_group_ref_id = $a_val;
	}
	
	/**
	 * Get item group ref id
	 *
	 * @return int item group ref id
	 */
	function getItemGroupRefId()
	{
		return $this->item_group_ref_id;
	}
	
	/**
	 * Set items
	 *
	 * @param array $a_val items (array of ref ids)	
	 */
	function setItems($a_val)
	{
		$this->items = $a_val;
	}
	
	/**
	 * Get items
	 *
	 * @return array items (array of ref ids)
	 */
	function getItems()
	{
		return $this->items;
	}
	
	/**
	 * Add one item
	 *
	 * @param int $a_item_ref_id item ref id 
	 */
	public function addItem($a_item_ref_id)
	{
		if (!in_array($a_item_ref_id, $this->items))
		{
			$this->items[] = (int) $a_item_ref_id;
		}
	}
	
	/**
	 * Delete items of item group
	 */
	function delete()
	{
		$query = "DELETE FROM item_group_item ".
			"WHERE item_group_id = ".$this->db->quote($this->getItemGroupId(), 'integer');
		$this->db->manipulate($query);
	}

	/**
	 * Update item group items
	 */
	function update()
	{
		$this->delete();
		
		foreach($this->items as $item)
		{
			$query = "INSERT INTO item_group_item (item_group_id,item_ref_id) ".
				"VALUES( ".
				$this->db->quote($this->getItemGroupId() ,'integer').", ".
				$this->db->quote($item ,'integer')." ".
				")";
			$this->db->manipulate($query);
		}
	}

	/**
	 * Read item group items
	 */
	public function read()
	{
		$this->items = array();
		$set = $this->db->query("SELECT * FROM item_group_item ".
			" WHERE item_group_id = ".$this->db->quote($this->getItemGroupId(), "integer")
			);
		while ($rec = $this->db->fetchAssoc($set))
		{
			$this->items[] = $rec["item_ref_id"];
		}
	}

	/**
	 * Get assignable items
	 *
	 * @param
	 * @return
	 */
	function getAssignableItems()
	{
		if ($this->getItemGroupRefId() <= 0)
		{
			return array();
		}
		
		$parent_node = $this->tree->getNodeData(
			$this->tree->getParentId($this->getItemGroupRefId()));
		
		$materials = array();
		$nodes = $this->tree->getChilds($parent_node["child"]);

		include_once("./Modules/File/classes/class.ilObjFileAccess.php");
		foreach($nodes as $node)
		{
			// filter side blocks and session, item groups and role folder
			if ($node['child'] == $parent_node["child"] ||
				$this->obj_def->isSideBlock($node['type']) ||
				in_array($node['type'], array('sess', 'itgr', 'rolf', 'adm')))
			{
				continue;
			}
			
			// filter hidden files
			// see http://www.ilias.de/mantis/view.php?id=10269
			if ($node['type'] == "file" &&
				ilObjFileAccess::_isFileHidden($node['title']))
			{
				continue;
			}

			$materials[] = $node;
		}
		
		$materials = ilUtil::sortArray($materials, "title", "asc");
		
		return $materials;
	}

	
	/**
	 * Get valid items
	 *
	 * @param
	 * @return
	 */
	function getValidItems()
	{
		$items = $this->getItems();
		$ass_items = $this->getAssignableItems();
		$valid_items = array();
		foreach ($ass_items as $aitem)
		{
			if (in_array($aitem["ref_id"], $items))
			{
				$valid_items[] = $aitem["ref_id"];
			}
		}
		return $valid_items;
	}
	
}
?>