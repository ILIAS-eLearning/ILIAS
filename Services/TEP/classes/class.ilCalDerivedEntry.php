<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived calendar entry type application class
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 * 
 * @ingroup ServicesTEP
 */
class ilCalDerivedEntry 
{
	protected $id; // [int]
	protected $master_entry_id; // [int]
	protected $cat_id; // [int]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_id
	 * @return self
	 */
	public function __construct($a_id = null)
	{
		if($a_id)
		{
			$this->read($a_id);
		}
	}
	
	//
	// properties
	// 
	
	/**
	 * Set id
	 * 
	 * @param int
	 */
	protected function setId($a_id)
	{
		$this->id = (int)$a_id;
	}
	
	/**
	 * Get id
	 * 
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set master entry
	 * 
	 * @param int $a_id
	 */
	public function setMasterEntryId($a_id)
	{
		$this->master_entry_id = (int)$a_id;
	}
	
	/**
	 * Get master entry
	 * 
	 * @return int
	 */
	public function getMasterEntryId()
	{
		return $this->master_entry_id;
	}
	
	/**
	 * Set category/calendar id
	 * 
	 * @param int $a_id
	 */
	public function setCategoryId($a_id)
	{
		$this->cat_id = (int)$a_id;
	}
	
	/**
	 * Get category/calendar id 
	 * 
	 * @return int
	 */
	public function getCategoryId()
	{
		return $this->cat_id;
	}
	
	
	//
	// CRUD
	//
	
	// :TODO: events ?!
	
	/**
	 * Read from DB
	 * 
	 * @param int $a_id
	 * @return boolean
	 */
	protected function read($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM cal_derived_entry".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		if($row["id"] == $a_id)
		{
			$this->setId($a_id);
			$this->setMasterEntryId($row["master_cal_entry"]);
			$this->setCategoryId($row["cat_id"]);
			return false;
		}
		
		return false;
	}
	
	/**
	 * Create DB entry
	 * 
	 * @return bool
	 */
	public function create()
	{
		global $ilDB;
		
		if($this->getId() ||
			!$this->getMasterEntryId() ||
			!$this->getCategoryId())
		{
			return;						
		}
		
		$this->setId($ilDB->nextId('cal_derived_entry'));		
		
		$fields = array(
			"id" => array("integer", $this->getId()),
			"master_cal_entry" => array("integer", $this->getMasterEntryId()),
			"cat_id" => array("integer", $this->getCategoryId())			
		);
		
		$ilDB->insert("cal_derived_entry", $fields);	
		return true;
	}
	
	/**
	 * Delete DB entry
	 */
	public function delete()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM cal_derived_entry".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));		
	}
	
	
	//
	// desctructor
	// 
	
	/**
	 * Delete all DB entries for category/calendar
	 *
	 * @param int $a_id
	 */
	public static function deleteByCategoryId($a_id)
	{
		global $ilDB;
		
		if(!(int)$a_id)
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM cal_derived_entry".
			" WHERE cat_id = ".$ilDB->quote($a_id, "integer"));		
	}
	
	/**
	 * Delete all DB entries for master entry
	 *
	 * @param int $a_id
	 */
	public static function deleteByMasterEntryId($a_id)
	{
		global $ilDB;
		
		if(!(int)$a_id)
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM cal_derived_entry".
			" WHERE master_cal_entry = ".$ilDB->quote($a_id, "integer"));		
	}
	
	
	//
	// find
	//
	
	/**
	 * Get categories/calendars by master entry
	 * 
	 * @param int $a_id
	 * @return array
	 */
	public static function getCategoryIdByMasterEntryId($a_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT cat_id".
			" FROM cal_derived_entry".
			" WHERE master_cal_entry = ".$ilDB->quote($a_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["cat_id"];			
		}		
		
		return $res;
	}	
		
	/**
	 * Get entries by master entry
	 * 
	 * @param int $a_id
	 * @return array
	 */
	public static function getIdByMasterEntryId($a_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT id".
			" FROM cal_derived_entry".
			" WHERE master_cal_entry = ".$ilDB->quote($a_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["id"];			
		}		
		
		return $res;
	}	
		
	/**
	 * Get users by master entry
	 * 
	 * @param array $a_ids
	 * @return array
	 */
	public static function getUserIdsByMasterEntryIds(array $a_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$query = "SELECT up.usr_id, cd.id, cd.master_cal_entry FROM usr_pref up".
			" JOIN cal_derived_entry cd ON (up.value = cd.cat_id)".
			" WHERE up.keyword = ".$ilDB->quote("tep_cat_id", "text").
			" AND ".$ilDB->in("cd.master_cal_entry", $a_ids, "", "integer");
		$set = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["master_cal_entry"]][$row["usr_id"]] = $row["id"];			
		}
		
		return $res;		
	}
}
