<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Tracking/classes/collection/class.ilLPCollection.php";

/**
* LP collection of repository objects
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPCollections.php 40326 2013-03-05 11:39:24Z jluetzen $
*
* @ingroup ServicesTracking
*/
class ilLPCollectionOfRepositoryObjects extends ilLPCollection
{
	protected static $possible_items = array(); 
	
	public function getPossibleItems($a_ref_id, $a_full_data = false)
	{
		global $tree, $objDefinition;	
		
		$cache_idx = $a_ref_id."__".$a_full_data;
		
		if(!isset(self::$possible_items[$cache_idx]))
		{		
			$all_possible = array();

			if(!$tree->isDeleted($a_ref_id))
			{
				include_once 'Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php';		

				if(!$a_full_data)
				{
					$data = $tree->getRbacSubTreeInfo($a_ref_id);
				}
				else
				{
					$node = $tree->getNodeData($a_ref_id);
					$data = $tree->getSubTree($node);
				}
				foreach($data as $node)
				{
					if(!$a_full_data)
					{
						$item_ref_id = $node['child'];
					}
					else
					{
						$item_ref_id = $node['ref_id'];
					}
					
					// avoid recursion
					if($item_ref_id == $a_ref_id ||
						!$this->validateEntry($item_ref_id, $node['type']))
					{
						continue;
					}
					
					switch($node['type'])
					{
						case 'sess':
						case 'exc':
						case 'fold':
						case 'grp':
						case 'sahs':
						case 'lm':
						case 'tst':
						case 'htlm':
							if(!$a_full_data)
							{
								$all_possible[] = $item_ref_id;
							}
							else
							{
								$all_possible[$item_ref_id] = array(
									'ref_id' => $item_ref_id,
									'obj_id' => $node['obj_id'],
									'title' => $node['title'],
									'description' => $node['description'],
									'type' => $node['type']
								);
							}
							break;

						// repository plugin object?
						case $objDefinition->isPluginTypeName($node['type']):							
							$only_active = false;
							if(!$this->isAssignedEntry($item_ref_id))
							{
								$only_active = true;
							}
							if(ilRepositoryObjectPluginSlot::isTypePluginWithLP($node['type'], $only_active))
							{
								if(!$a_full_data)
								{
									$all_possible[] = $item_ref_id;
								}
								else
								{
									$all_possible[$item_ref_id] = array(
										'ref_id' => $item_ref_id,
										'obj_id' => $node['obj_id'],
										'title' => $node['title'],
										'description' => $node['description'],
										'type' => $node['type']
									);
								}
							}					
							break;
					}			
				}
			}
			
			self::$possible_items[$cache_idx] = $all_possible;
		}
		
		return self::$possible_items[$cache_idx];
	}			
	
	protected function validateEntry($a_item_ref_id, $a_item_type = null)
	{								
		if(!$a_item_type)
		{
			$a_item_type = ilObject::_lookupType($a_item_ref_id, true);
		}
		
		// this is hardcoded so we do not need to call all ObjectLP types
		if($a_item_type == 'tst')
		{
			// Check anonymized
			$item_obj_id = ilObject::_lookupObjId($a_item_ref_id);
			$olp = ilObjectLP::getInstance($item_obj_id);
			if($olp->isAnonymized())
			{
				return false;
			}
		}							
		return true;
	}
	
	public function cloneCollection($a_target_id, $a_copy_id)
	{	
		parent::cloneCollection($a_target_id, $a_copy_id);
		
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
		$mappings = $cwo->getMappings();
		
		$target_obj_id = ilObject::_lookupObjId($a_target_id);
		$target_collection = new static($target_obj_id, $this->mode);
		
		// clone (active) groupings
		foreach($this->getGroupedItemsForLPStatus() as $group)
		{
			$target_item_ids = array();
			foreach($group["items"] as $item)
			{
				if(!isset($mappings[$item]) or !$mappings[$item])
				{
					continue;
				}

				$target_item_ids[] = $mappings[$item];	 	
			}
			
			// single item left after copy?
			if(sizeof($target_item_ids) > 1)
			{
				// should not be larger than group
				$num_obligatory = min(sizeof($target_item_ids), $group["num_obligatory"]);
				
				$target_collection->createNewGrouping($target_item_ids, $num_obligatory);
			}
		}
	}
	
	
	//
	// CRUD
	//
	
	protected function read()
	{
		global $ilDB;
		
		$items = array();
		
		$ref_ids = ilObject::_getAllReferences($this->obj_id);
		$ref_id = end($ref_ids);
		$possible = $this->getPossibleItems($ref_id);
		
		$res = $ilDB->query("SELECT utc.item_id, obd.type".
			" FROM ut_lp_collections utc".
			" JOIN object_reference obr ON item_id = ref_id".
			" JOIN object_data obd ON obr.obj_id = obd.obj_id".
			" WHERE utc.obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND active = ".$ilDB->quote(1, "integer").
			" ORDER BY title");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(in_array($row->item_id, $possible) &&
				$this->validateEntry($row->item_id, $row->type))
			{
				$items[] = $row->item_id;
			}
			else
			{
				$this->deleteEntry($row->item_id);
			}
		}
		
		$this->items = $items;
	}	
	
	protected function addEntry($a_item_id)
	{
		global $ilDB;
		
		// only active entries are assigned!
		if(!$this->isAssignedEntry($a_item_id))
		{
			// #13278 - because of grouping inactive items may exist
			$this->deleteEntry($a_item_id);
			
			$query = "INSERT INTO ut_lp_collections".
				" (obj_id, lpmode, item_id, grouping_id, num_obligatory, active)".
				" VALUES (".$ilDB->quote($this->obj_id , "integer").
				", ".$ilDB->quote($this->mode, "integer").
				", ".$ilDB->quote($a_item_id , "integer").
				", ".$ilDB->quote(0, "integer").
				", ".$ilDB->quote(0, "integer").
				", ".$ilDB->quote(1, "integer").
				")";
			$ilDB->manipulate($query);
			$this->items[] = $a_item_id;
		}
		return true;
	}
	
	protected function deleteEntry($a_item_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM ut_lp_collections ".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND item_id = ".$ilDB->quote($a_item_id, "integer").
			" AND grouping_id = ".$ilDB->quote(0, "integer");
		$ilDB->manipulate($query);
		return true;
	}
	
	
	//
	// GROUPING
	// 

	public static function hasGroupedItems($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT item_id FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND grouping_id > ".$ilDB->quote(0, "integer");
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	protected function getGroupingIds(array $a_item_ids)
	{
		global $ilDB;
		
		$grouping_ids = array();
		
		$query = "SELECT grouping_id FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND ".$ilDB->in("item_id", $a_item_ids, false, "integer").
			" AND grouping_id > ".$ilDB->quote(0, "integer");
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$grouping_ids[] = $row->grouping_id;
		}
		
		return $grouping_ids;
	}

	public function deactivateEntries(array $a_item_ids)
	{
		global $ilDB;

		parent::deactivateEntries($a_item_ids);
	
		$grouping_ids = $this->getGroupingIds($a_item_ids);
		if($grouping_ids)
		{
			$query = "UPDATE ut_lp_collections".
				" SET active = ".$ilDB->quote(0, "integer").
				" WHERE ".$ilDB->in("grouping_id", $grouping_ids, false, "integer").
				" AND obj_id = ".$ilDB->quote($this->obj_id, "integer");
			$ilDB->manipulate($query);			
		}
	}

	public function activateEntries(array $a_item_ids)
	{
		global $ilDB;
		
		parent::activateEntries($a_item_ids);
		
		$grouping_ids = $this->getGroupingIds($a_item_ids);
		if($grouping_ids)
		{
			$query = "UPDATE ut_lp_collections".
				" SET active = ".$ilDB->quote(1, "integer").
				" WHERE ".$ilDB->in("grouping_id", $grouping_ids, false, "integer").
				" AND obj_id = ".$ilDB->quote($this->obj_id, "integer");
			$ilDB->manipulate($query);
		}
	}

	public function createNewGrouping(array $a_item_ids, $a_num_obligatory = 1)
	{
		global $ilDB;

		$this->activateEntries($a_item_ids);
		
		$all_item_ids = array();
		
		$grouping_ids = $this->getGroupingIds($a_item_ids);

		$query = "SELECT item_id FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND ".$ilDB->in("grouping_id", $grouping_ids, false, "integer");
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$all_item_ids[] = $row->item_id;
		}

		$all_item_ids = array_unique(array_merge($all_item_ids, $a_item_ids));

		$this->releaseGrouping($a_item_ids);

		// Create new grouping
		$query = "SELECT MAX(grouping_id) grp FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" GROUP BY obj_id";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		$grp_id = $row->grp;		
		++$grp_id;

		$query = "UPDATE ut_lp_collections SET".
			" grouping_id = ".$ilDB->quote($grp_id, "integer").
			", num_obligatory = ".$ilDB->quote($a_num_obligatory, "integer").
			", active = ".$ilDB->quote(1, "integer").
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND ".$ilDB->in("item_id", $all_item_ids, false, "integer");
		$ilDB->manipulate($query);

		return;
	}
	
	public function releaseGrouping(array $a_item_ids)
	{
		global $ilDB;
		
		$grouping_ids = $this->getGroupingIds($a_item_ids);

		$query = "UPDATE ut_lp_collections".
			" SET grouping_id = ".$ilDB->quote(0, "integer").
			", num_obligatory = ".$ilDB->quote(0, "integer").
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND " . $ilDB->in("grouping_id", $grouping_ids, false, "integer");
		$ilDB->manipulate($query);
	}

	public function saveObligatoryMaterials(array $a_obl)
	{
		global $ilDB;

		foreach($a_obl as $grouping_id => $num)
		{
			$query = "SELECT count(obj_id) num FROM ut_lp_collections".
				" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
				" AND grouping_id = ".$ilDB->quote($grouping_id,'integer').
				" GROUP BY obj_id";
			$res = $ilDB->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if($num <= 0 || $num >= $row->num)
				{
					throw new UnexpectedValueException();
				}
			}
		}
		foreach($a_obl as $grouping_id => $num)
		{
			$query = "UPDATE ut_lp_collections".
				" SET num_obligatory = ".$ilDB->quote($num, "integer").
				" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
				" AND grouping_id = ".$ilDB->quote($grouping_id, "integer");
			$ilDB->manipulate($query);
		}
	}

	
	//
	// TABLE GUI
	// 
	
	public function getTableGUIData($a_parent_ref_id)
	{	
		$items = $this->getPossibleItems($a_parent_ref_id, true);
	
		$data = array();
		$done = array();
		foreach($items as $item_id => $item)
		{			
			if(in_array($item_id, $done))
			{
				continue;
			}
			
			$table_item = $this->parseTableGUIItem($item_id, $item);
						
			// grouping			
			$table_item['grouped'] = array();						
			$grouped_items = $this->getTableGUItemGroup($item_id);
			if(count((array)$grouped_items['items']) > 1)
			{
				foreach($grouped_items['items'] as $grouped_item_id)
				{
					if($grouped_item_id == $item_id)
					{
						continue;
					}
					
					$table_item['grouped'][] = $this->parseTableGUIItem($grouped_item_id, $items[$grouped_item_id]);
					$table_item['num_obligatory'] = $grouped_items['num_obligatory'];
					$table_item['grouping_id'] = $grouped_items['grouping_id'];
					
					$done[] = $grouped_item_id;
				}
			}
			
			$data[] = $table_item;
		}
		
		return $data;
	}
	
	protected function parseTableGUIItem($a_id, array $a_item)
	{		
		$table_item = $a_item;						
		$table_item['id'] = $a_id;								
		$table_item['status'] = $this->isAssignedEntry($a_id);

		$olp = ilObjectLP::getInstance($a_item['obj_id']);
		$table_item['mode_id'] = $olp->getCurrentMode();
		$table_item['mode'] = $olp->getModeText($table_item['mode_id']);	
		$table_item['anonymized'] = $olp->isAnonymized();
		
		return $table_item;
	}
	
	protected function getTableGUItemGroup($item_id)
	{
		global $ilDB;
		
		$items = array();

		$query = "SELECT grouping_id FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND item_id = ".$ilDB->quote($item_id, "integer");
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		$grouping_id = $row->grouping_id;
		if($grouping_id > 0)
		{			
			$query = "SELECT item_id, num_obligatory FROM ut_lp_collections".
				" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
				" AND grouping_id = ".$ilDB->quote($grouping_id, "integer");
			$res = $ilDB->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$items['items'][] = $row->item_id;
				$items['num_obligatory'] = $row->num_obligatory;
				$items['grouping_id'] = $grouping_id;
			}
		}
		
		return $items;
	}
	
	public function getGroupedItemsForLPStatus()
	{
		global $ilDB;

		$items = $this->getItems();

		$query = " SELECT * FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND active = ".$ilDB->quote(1, "integer");
		$res = $ilDB->query($query);

		$grouped = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(in_array($row->item_id, $items))
			{
				$grouped[$row->grouping_id]['items'][] = $row->item_id;
				$grouped[$row->grouping_id]['num_obligatory'] = $row->num_obligatory;
			}
		}
		
		return $grouped;
	}
}

?>