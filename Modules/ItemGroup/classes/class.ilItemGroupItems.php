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
	var $items = array();

	/**
	 * Constructor
	 *
	 * @param int $a_item_group_id object id of item group
	 */
	function ilItemGroupItems($a_item_group_id = 0)
	{
		global $ilDB, $lng;

		$this->db  = $ilDB;
		$this->lng = $lng;

		$this->item_group_id = $a_item_group_id;

		if ($a_item_group_id > 0)
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
	protected function read()
	{
		$set = $this->db->query("SELECT * FROM item_group_item ".
			" WHERE item_group_id = ".$this->db->quote($this->getItemGroupId(), "integer")
			);
		while ($rec = $this->db->fetchAssoc($set))
		{
			$this->items[] = $rec["item_ref_id"];
		}
	}

}
?>