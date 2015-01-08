<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEP.php";
require_once "Services/TEP/classes/class.ilCalDerivedEntry.php";
require_once "Services/Calendar/classes/class.ilCalendarEntry.php";
require_once "Services/Calendar/classes/class.ilCalendarCategoryAssignments.php";

/**
 * TEP entry application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPEntry extends ilCalendarEntry
{
	protected $owner_id; // [int]
	protected $derived_user_ids; // [array]	
	protected $type_title; // [string]
		
	const OPERATION_DAY_ID = "tep_entry";	
	
	
	
	//
	// properties
	//		
		
	/**
	 * Set owner
	 * 
	 * @param int $a_value
	 */
	public function setOwnerId($a_value)
	{
		$this->owner_id = (int)$a_value;
	}
	
	/**
	 * Get owner
	 * 
	 * @return int
	 */
	public function getOwnerId()
	{
		return $this->owner_id;
	}
	
	/**
	 * Set derived user ids
	 * 
	 * @param array $a_user_ids
	 */
	public function setDerivedUsers(array $a_user_ids = null)
	{
		return $this->derived_user_ids = $a_user_ids;		
	}
	
	/**
	 * Get derived user ids
	 * 
	 * @return array
	 */
	public function getDerivedUsers()
	{
		if(is_array($this->derived_user_ids))
		{
			return $this->derived_user_ids;
		}	
	}
	
	// gev-patch start
	public function getTypeTitle() {
		if ($this->type_title !== null) {
			return $this->type_title;
		}
		
		require_once("Services/TEP/classes/class.ilCalEntryType.php");
		$t = new ilCalEntryType($this->getType());
		$this->type_title = $t->getTitle();

		return $this->type_title;
	}
	// gev-patch end
	
	
	//
	// CRUD
	//
	
	/**
	 * Read from DB
	 */
	protected function read()
	{
		parent::read();
		
		$this->setOwnerId(ilTEP::findUserByEntryId($this->getEntryId()));
		
		$tmp = ilCalDerivedEntry::getUserIdsByMasterEntryIds(array($this->getEntryId()));
		if(sizeof($tmp))
		{			
			$this->setDerivedUsers(array_keys($tmp[$this->getEntryId()]));
		}
	}
	
	/**
	 * Handle entry owners after updates
	 * 
	 * @param int $a_entry_id
	 * @param bool $a_handle_derived
	 * @param bool $a_is_update
	 */
	protected function handleEntryOwners($a_entry_id, $a_handle_derived = true, $a_is_update = false)
	{
		// owner calendar	
		
		if($this->getOwnerId())
		{
			$cal_cat_id = ilTEP::getPersonalCalendarId($this->getOwnerId());

			// did owner change?
			$ass = new ilCalendarCategoryAssignments($a_entry_id);		
			if((bool)$a_is_update)
			{
				if($ass->getFirstAssignment() != $cal_cat_id)
				{
					$ass->deleteAssignments();
					$ass->addAssignment($cal_cat_id);
				}
			}
			else
			{
				$ass->addAssignment($cal_cat_id);
			}
		}

		// derived entries
		if((bool)$a_handle_derived)
		{			
			if((bool)$a_is_update) // #153
			{
				ilCalDerivedEntry::deleteByMasterEntryId($a_entry_id);
			}
			
			$derived = $this->getDerivedUsers();
			if($derived)
			{
				foreach($derived as $user_id)
				{
					if($user_id == $this->getOwnerId())
					{
						continue;
					}

					$drv_entry = new ilCalDerivedEntry();
					$drv_entry->setCategoryId(ilTEP::getPersonalCalendarId($user_id));
					$drv_entry->setMasterEntryId($a_entry_id);
					$drv_entry->create();
				}
			}
		}
	}
	
	/**
	 * Validate properties
	 * 
	 * @return boolean
	 */
	public function validate()
	{
		$res = parent::validate();			
		if($this->getOwnerId() === null)
		{
			return false;
		}
		return $res;
	}
	
	/**
	 * Create DB entry
	 * 
	 * @param bool $a_handle_derived
	 * @return boolean
	 */
	public function save($a_handle_derived = true)
	{
		if($this->getEntryId())
		{
			return;
		}
		
		if($this->validate() && 
			parent::save())
		{			
			$this->handleEntryOwners($this->getEntryId(), $a_handle_derived);			
			
			self::raiseEvent("create", $this);
			
			return true;
		}
		
		return false;				
	}
	
	/**
	 *  Update DB entry
	 * 
	 * @param bool $a_handle_derived
	 * @return bool
	 */
	public function update($a_handle_derived = true)
	{		
		if(!$this->getEntryId())
		{
			return;
		}
		
		if($this->validate() && 
			parent::update())
		{
			$this->handleEntryOwners($this->getEntryId(), $a_handle_derived, true);
			
			self::raiseEvent("update", $this);
			
			return true;
		}
		
		return false;
	}
		
	/**
	 * Delete DB entry
	 */
	public function delete()
	{	
		if(!$this->getEntryId())
		{
			return;
		}
		
		ilCalDerivedEntry::deleteByMasterEntryId($this->getEntryId());		
		ilCalendarCategoryAssignments::_deleteByAppointmentId($this->getEntryId());
		
		require_once "Services/TEP/classes/class.ilTEPOperationDays.php";
		ilTEPOperationDays::deleteByObject(self::OPERATION_DAY_ID, $this->getEntryId());
		
		parent::delete();
		
		self::raiseEvent("delete", $this);
	}
	
	
	//
	// find
	//
	
	/**
	 * Get entry id by context
	 *
	 * @param type $a_context_id
	 * @return int
	 */
	public static function getEntryByContextId($a_context_id)
	{
		global $ilDB;
		
		$sql = "SELECT cal_id FROM cal_entries".
			" WHERE context_id = ".$ilDB->quote($a_context_id, "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$row = $ilDB->fetchAssoc($set);
			return $row["cal_id"];
		}
	}
	
	/**
	 * Get all (used) entry types
	 * 
	 * @return array
	 */	
	public static function getAllTypesInUse()
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT DISTINCT(entry_type) type FROM cal_entries";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["type"];
		}
		
		return $res;
	}
	
	
	//
	// events
	//
	
	/**
	 * Raise event
	 * 	 
	 * @param string $a_event
	 * @param ilTEPEntry $a_entry
	 */
	protected static function raiseEvent($a_event, ilTEPEntry $a_entry)
	{
		global $ilAppEventHandler;
		
		$params = array("entry"=>$a_entry);
		
		$ilAppEventHandler->raise("Services/TEP", $a_event, $params);
	}
	
}
