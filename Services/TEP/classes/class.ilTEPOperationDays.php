<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TEP operation days application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPOperationDays
{
	protected $object_type; // [string]
	protected $object_id; // [int]
	protected $from; // [ilDate]
	protected $to; // [ilDate]
	
	/**
	 * Constructor
	 * 
	 * @param string $a_object_type
	 * @param int $a_object_id
	 * @param ilDate $a_from
	 * @param ilDate $_ato
	 */
	public function __construct($a_object_type, $a_object_id, ilDate $a_from, ilDate $a_to)
	{
		$this->setObjectType($a_object_type);
		$this->setObjectId($a_object_id);
		$this->setPeriod($a_from, $a_to);
	}
	
	//
	// properties
	//
	
	/**
	 * Set object type
	 * 
	 * @param string $a_value
	 */
	protected function setObjectType($a_value)
	{
		$this->object_type = trim($a_value);
	}
	
	/**
	 * Get object type
	 * 
	 * @return string
	 */
	protected function getObjectType()
	{
		return $this->object_type;
	}
	
	/**
	 * Set object id
	 * 
	 * @param int $a_value
	 */
	protected function setObjectId($a_value)
	{
		$this->object_id = (int)$a_value;
	}
	
	/**
	 * Get object id
	 * 
	 * @return int
	 */
	protected function getObjectId()
	{
		return $this->object_id;
	}
	
	/**
	 * Set start and end date
	 * 
	 * @param ilDate $a_start
	 * @param ilDate $a_end
	 */
	protected function setPeriod(ilDate $a_start, ilDate $a_end)
	{
		if(ilDate::_after($a_start, $a_end))
		{
			$this->start = $a_end;
			$this->end = $a_start;
		}
		else
		{
			$this->start = $a_start;
			$this->end = $a_end;
		}		
	}
	
	/**
	 * Get start date
	 * 
	 * @return ilDate
	 */
	public function getStart()
	{
		return $this->start;
	}
	
	/**
	 * Get start date
	 * 
	 * @return ilDate
	 */
	public function getEnd()
	{
		return $this->end;
	}
	
	/**
	 * Get valid days
	 * 
	 * @return array
	 */
	public function getValidDays()
	{
		$res = array();
	
		$counter = 0;
		$current = clone $this->getStart();		
		while((ilDate::_before($current, $this->getEnd()) || ilDate::_equals($current, $this->getEnd())) && 
			$counter < 100)
		{			
			$res[] = clone $current;
			
			$current->increment(IL_CAL_DAY, 1);
			$counter++;			
		}
		
		return $res;
	}
	
	//
	// CRUD
	// 
	
	/**
	 * 
	 */
	public function getDaysForUsers(array $a_user_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$valid = $this->getValidDays();
		$valid_comp = array();
		foreach($valid as $idx => $day)
		{
			$valid_comp[$idx] = $day->get(IL_CAL_DATE);
		}
		
		$missing = array();
		
		$sql = "SELECT user_id,miss_day".
			" FROM tep_op_days".
			" WHERE ".$ilDB->in("user_id", $a_user_ids, "", "integer").
			" AND ".$this->getObjectWhere();
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$missing[$row["user_id"]][] = $row["miss_day"];			
		}
		
		$res = array();
	    foreach($a_user_ids as $user_id)
		{
			// no missing days
			if(!array_key_exists($user_id, $missing))
			{
				$res[$user_id] = $valid;
			}
			// remove missing days
			else
			{
				$res[$user_id] = array();
				foreach($valid_comp as $idx => $day)
				{
					if(!in_array($day, $missing[$user_id]))
					{
						$res[$user_id][] = $valid[$idx]; 
					}
				}				
			}
		}
		
		return $res;
	}
	
	/**
	 * Get SQL where condition for current object
	 * 
	 * @param int $a_user_id 
	 * @return string
	 */
	protected function getObjectWhere($a_user_id = null)
	{
		global $ilDB;
		
		$sql = "obj_type = ".$ilDB->quote($this->getObjectType(), "text").
			" AND obj_id = ".$ilDB->quote($this->getObjectId(), "integer");
		if($a_user_id)
		{
			$sql .= " AND user_id = ".$ilDB->quote($a_user_id, "integer");
		}
		return $sql;
	}
	
	/**
	 * Create DB entry for missing day
	 * 
	 * @param int $a_user_id
	 * @param ilDate $a_day
	 * @return bool
	 */
	protected function insertEntry($a_user_id, ilDate $a_day)
	{
		global $ilDB;
		
		$fields = array(
			"obj_type" => array("text", $this->getObjectType())
			,"obj_id" => array("integer", $this->getObjectId())
			,"user_id" => array("integer", $a_user_id)
			,"miss_day" => array("date", $a_day->get(IL_CAL_DATE))
		);		
		$ilDB->insert("tep_op_days", $fields);
	}
	
	/**
	 * Delete DB entries for user
	 * 
	 * @param int $a_user_id
	 */
	protected function deleteEntries($a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM tep_op_days".
			" WHERE ".$this->getObjectWhere($a_user_id));
	}
	
	/**
	 * Set all days as missing
	 * 
	 * @param int $a_user_id
	 */
	public function setNoDaysForUser($a_user_id)
	{
		$this->deleteEntries($a_user_id);
		
		foreach($this->getValidDays() as $day)
		{
			$this->insertEntry($a_user_id, $day);
		}
		
		// gev-patch start
		if ($this->getObjectType() == "tep_entry") {
			require_once("Services/TEP/classes/class.ilTEPEntry.php");
			$entry = new ilTEPEntry($this->getObjectId());
			$entry->update();
		}
		// gev-patch end
	}
	
	/**
	 * Set no missing days for user
	 */
	public function setAllDaysForUser($a_user_id)
	{
		$this->deleteEntries($a_user_id);
				
		// gev-patch start
		if ($this->getObjectType() == "tep_entry") {
			require_once("Services/TEP/classes/class.ilTEPEntry.php");
			$entry = new ilTEPEntry($this->getObjectId());
			$entry->update();
		}
		// gev-patch end
	}
	
	/**
	 * Set operation days for user
	 * 
	 * @param int $a_user_id
	 * @param array $a_days
	 */
	public function setDaysForUser($a_user_id, array $a_days)
	{
		$this->deleteEntries($a_user_id);
		
		// array may consist of ilDate
		foreach($a_days as $idx => $day)
		{
			if($day instanceof ilDate)
			{
				$a_days[$idx] = $day->get(IL_CAL_DATE);
 			}
		}
		
		foreach($this->getValidDays() as $day)
		{
			$date = $day->get(IL_CAL_DATE);
			if(!in_array($date, $a_days))
			{			
				$this->insertEntry($a_user_id, $day);
			}
		}
		
		// gev-patch start
		if ($this->getObjectType() == "tep_entry") {
			require_once("Services/TEP/classes/class.ilTEPEntry.php");
			$entry = new ilTEPEntry($this->getObjectId());
			$entry->update();
		}
		// gev-patch end
	}
	
	/**
	 * Get user operation days
	 * 
	 * @param int $a_user_id
	 * @return array
	 */
	public function getDaysForUser($a_user_id)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT miss_day".
			" FROM tep_op_days".
			" WHERE ".$this->getObjectWhere($a_user_id);
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$missing = array();
			while($row = $ilDB->fetchAssoc($set))
			{
				$missing[] = $row["miss_day"];			
			}
			
			foreach($this->getValidDays() as $day)
			{
				$date = $day->get(IL_CAL_DATE);
				if(!in_array($date, $missing))
				{
					$res[] = $day;
				}
			}
			
		}
		else
		{
			$res = $this->getValidDays();
		}
		
		return $res;
	}
	
	
	//
	// destructor
	// 
	
	/**
	 * Delete all entries for user
	 * 
	 * @param int $a_user_id
	 */
	public static function deleteByUserId($a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM tep_op_days".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer"));
	}
	
	/**
	 * Delete all entries for object
	 * 
	 * @param string $a_object_type
	 * @param int $a_object_id
	 */
	public static function deleteByObject($a_object_type, $a_object_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM tep_op_days".
			" WHERE obj_type = ".$ilDB->quote($a_object_type, "text").
			" AND obj_id = ".$ilDB->quote($a_object_id, "integer"));		
	}
}
