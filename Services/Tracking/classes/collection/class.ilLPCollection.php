<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* LP collection base class
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPCollections.php 40326 2013-03-05 11:39:24Z jluetzen $
*
* @ingroup ServicesTracking
*/
abstract class ilLPCollection
{
	protected $obj_id; // [int]
	protected $mode; // [int]
	protected $items; // [array]

	public function __construct($a_obj_id, $a_mode)
	{
		$this->obj_id = $a_obj_id;
		$this->mode = $a_mode;	
		
		if($a_obj_id)
		{
			$this->read($a_obj_id);
		}
	}
	
	public function hasSelectableItems()
	{
		return true;
	}
	
	public function cloneCollection($a_target_id, $a_copy_id)
	{
		global $ilLog;
		
		$target_obj_id = ilObject::_lookupObjId($a_target_id);
		
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
		$mappings = $cwo->getMappings();
		
		// #12067
		$new_collection = new static($target_obj_id, $this->mode);
	 	foreach($this->items as $item)
	 	{
	 		if(!isset($mappings[$item]) or !$mappings[$item])
	 		{
	 			continue;
	 		}
			
	 		$new_collection->addEntry($mappings[$item]);	 		
	 	}
		
		$ilLog->write(__METHOD__.': cloned learning progress collection.');
	}
	
	
	//
	// CRUD
	//
	
	public function getItems()
	{
		return $this->items;
	}
	
	protected function read()
	{
		global $ilDB;
		
		$items = array();
		
		$res = $ilDB->query("SELECT * FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer"));
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($this->validateEntry($row->item_id))
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
	
	public function delete()
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id ,"integer");
		$ilDB->manipulate($query);
		
		$query = "DELETE FROM ut_lp_coll_manual".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer");
		$ilDB->manipulate($query);

		return true;
	}

	//
	// ENTRIES
	//
			
	protected function validateEntry($a_item_id)
	{
		return true;
	}			
		
	public function isAssignedEntry($a_item_id)
	{
		if(is_array($this->items))
		{
			return (bool)in_array($a_item_id, $this->items);
		}
		return false;
	}

	protected function addEntry($a_item_id)
	{
		global $ilDB;
		
		if(!$this->isAssignedEntry($a_item_id))
		{
			$query = "INSERT INTO ut_lp_collections".
				" (obj_id, lpmode, item_id)".
				" VALUES (".$ilDB->quote($this->obj_id , "integer").
				", ".$ilDB->quote($this->mode, "integer").
				", ".$ilDB->quote($a_item_id , "integer").				
				")";
			$ilDB->manipulate($query);
			$this->items[] = $a_item_id;
		}
		return true;
	}
	
	protected function deleteEntry($a_item_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM ut_lp_collections".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND item_id = ".$ilDB->quote($a_item_id, "integer");
		$ilDB->manipulate($query);
		return true;
	}
	
	public function deactivateEntries(array $a_item_ids)
	{		
		foreach($a_item_ids as $item_id)
		{
			$this->deleteEntry($item_id);
		}		
	}

	public function activateEntries(array $a_item_ids)
	{		
		foreach($a_item_ids as $item_id)
		{
			$this->addEntry($item_id);		
		}		
	}
}

?>