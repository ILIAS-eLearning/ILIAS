<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LO courses user results 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @package ModulesCourse
 */
class ilLOUserResults
{
	protected $course_obj_id; // [int]
	protected $user_id; // [int]
	
	const TYPE_INITIAL = 1;
	const TYPE_QUALIFIED = 2;
	
	const STATUS_COMPLETED = 1;
	const STATUS_FAILED = 2;	
	
	/**
	 * Constructor
	 * 
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 * @return ilLOUserResults
	 */
	public function __construct($a_course_obj_id, $a_user_id)
	{
		$this->course_obj_id = (int)$a_course_obj_id;
		$this->user_id = (int)$a_user_id;
	}

	/**
	 * Lookup user result
	 */
	public static function lookupResult($a_course_obj_id, $a_user_id, $a_objective_id, $a_tst_type)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_user_results '.
				'WHERE user_id = '.$ilDB->quote($a_user_id,'integer').' '.
				'AND course_id = '.$ilDB->quote($a_course_obj_id,'integer').' '.
				'AND objective_id = '.$ilDB->quote($a_objective_id,'integer').' '.
				'AND type = '.$ilDB->quote($a_tst_type,'integer');
		$res = $ilDB->query($query);
		$ur = array(
			'status' => self::STATUS_FAILED,
			'result_perc' => 0,
			'limit_perc' => 0,
			'tries' => 0,
			'is_final' => 0
		);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ur['status'] = $row->status;
			$ur['result_perc'] = $row->result_perc;
			$ur['limit_perc'] = $row->limit_perc;
			$ur['tries'] = $row->tries;
			$ur['is_final'] = $row->is_final;
		}		
		return $ur;
	}
	
	public static function resetFinalByObjective($a_objective_id)
	{
		$query = 'UPDATE loc_user_results '.
				'SET is_final = '.$GLOBALS['ilDB']->quote(0,'integer').' '.
				'WHERE objective_id = '.$GLOBALS['ilDB']->quote($a_objective_id,'integer');
		$GLOBALS['ilDB']->manipulate($query);
	}
	

	/**
	 * Is given type valid?
	 * 
	 * @param int $a_type
	 * @return bool
	 */
	protected static function isValidType($a_type)
	{
		return in_array((int)$a_type, array(self::TYPE_INITIAL, self::TYPE_QUALIFIED));
	}
		
	/**
	 * Is given status valid?
	 * 
	 * @param int $a_status
	 * @return bool
	 */
	protected static function isValidStatus($a_status)
	{
		return in_array((int)$a_status, array(self::STATUS_COMPLETED, self::STATUS_FAILED));
	}
	
	/**
	 * Delete all result entries for user
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public static function deleteResultsForUser($a_user_id)
	{
		global $ilDB;
		
		if(!(int)$a_user_id)
		{
			return false;
		}
		
		$ilDB->manipulate("DELETE FROM loc_user_results".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer"));
		return true;
	}
	
	
	/**
	 * Delete all result entries for course
	 * 
	 * @param int $a_course_id
	 * @return bool
	 */
	public static function deleteResultsForCourse($a_course_id)
	{
		global $ilDB;
		
		if(!(int)$a_course_id)
		{
			return false;
		}
		
		$ilDB->manipulate("DELETE FROM loc_user_results".
			" WHERE course_id = ".$ilDB->quote($a_course_id, "integer"));
		return true;
	}
	
	/**
	 * Delete for user and course
	 * @global type $ilDB
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = 'DELETE FROM loc_user_results '.
				'WHERE course_id = '.$ilDB->quote($this->course_obj_id).' '.
				'AND user_id = '.$ilDB->quote($this->user_id);
		$ilDB->manipulate($query);
	}
	
	/**
	 * Delete all (qualified) result entries for course members
	 * 
	 * @param int $a_course_id
	 * @param array $a_user_ids
	 * @param bool $a_remove_initial
	 * @param bool $a_remove_qualified
	 * @return bool
	 */
	public static function deleteResultsFromLP($a_course_id, array $a_user_ids, $a_remove_initial, $a_remove_qualified)
	{
		global $ilDB;
		
		if(!(int)$a_course_id || !sizeof($a_user_ids))
		{
			return false;
		}
		
		$sql = "DELETE FROM loc_user_results".
			" WHERE course_id = ".$ilDB->quote($a_course_id, "integer").
			" AND ".$ilDB->in("user_id", $a_user_ids, "", "integer");
		
		if(!(bool)$a_remove_initial || !(bool)$a_remove_qualified)
		{
			if((bool)$a_remove_initial)
			{
				$sql .= " AND type = ".$ilDB->quote(self::TYPE_INITIAL, "integer");
			}
			else
			{
				$sql .= " AND type = ".$ilDB->quote(self::TYPE_QUALIFIED, "integer");
			}
		}
				
		$ilDB->manipulate($sql);
		return true;
	}
	
			
	/**
	 * Save objective result
	 * 
	 * @param int $a_objective_id
	 * @param int $a_type	
	 * @param int $a_status
	 * @param int $a_result_percentage
	 * @param int $a_limit_percentage
	 * @param int $a_tries
	 * @param bool $a_is_final
	 * @return bool
	 */
	public function saveObjectiveResult($a_objective_id, $a_type, $a_status, $a_result_percentage, $a_limit_percentage, $a_tries, $a_is_final)
	{
		global $ilDB;
		
		if(!self::isValidType($a_type) ||
			!self::isValidStatus($a_status))
		{
			return false;
		}
		$ilDB->replace("loc_user_results",
			array(
				"course_id" => array("integer", $this->course_obj_id),
				"user_id" => array("integer", $this->user_id),
				"objective_id" => array("integer", $a_objective_id),
				"type" => array("integer", $a_type)
			),
			array(
				"status" => array("integer", $a_status),
				"result_perc" => array("integer", $a_result_percentage),
				"limit_perc" => array("integer", $a_limit_percentage),
				"tries" => array("integer", $a_tries),
				"is_final" => array("integer", $a_is_final),
				"tstamp" => array("integer", time()),
			)
		);
		return true;
	}	
	
	/**
	 * Find objective ids by type and/or status
	 * 
	 * @param int $a_type
	 * @param int $a_status
	 * @param bool $a_is_final
	 * @return array
	 */
	protected function findObjectiveIds($a_type = null, $a_status = null, $a_is_final = null)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT objective_id".
			" FROM loc_user_results".
			" WHERE course_id = ".$ilDB->quote($this->course_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($this->user_id, "integer");
		
		if($this->isValidType($a_type))
		{
			$sql .= " AND type = ".$ilDB->quote($a_type, "integer");
		}
		if($this->isValidStatus($a_status))
		{
			$sql .= " AND status = ".$ilDB->quote($a_status, "integer");
		}		
		if($a_is_final !== null)
		{
			$sql .= " AND is_final = ".$ilDB->quote($a_is_final, "integer");
		}
		
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["objective_id"];
		}
		
		return $res;		
	}
	
	/**
	 * All completed objectives by type
	 * @param type $a_type
	 * @return type
	 */
	public function getCompletedObjectiveIdsByType($a_type)
	{
		return $this->findObjectiveIds($a_type, self::STATUS_COMPLETED);
	}
	
	/**
	 * Get all objectives where the user failed the initial test
	 * 
	 * @return array objective-ids
	 */
	public function getSuggestedObjectiveIds()
	{		
		return $this->findObjectiveIds(self::TYPE_INITIAL, self::STATUS_FAILED);		
	}
	
	/**
	 * Get all objectives where the user completed the qualified test
	 * 
	 * @return array objective-ids
	 */
	public function getCompletedObjectiveIds()
	{		
		return $this->findObjectiveIds(self::TYPE_QUALIFIED, self::STATUS_COMPLETED);		
	}
	
	/**
	 * Get all objectives where the user failed the qualified test
	 * 
	 * @param bool $a_is_final
	 * @return array objective-ids
	 */
	public function getFailedObjectiveIds($a_is_final = true)
	{		
		return $this->findObjectiveIds(self::TYPE_QUALIFIED, self::STATUS_FAILED, $a_is_final);		
	}
		
	/**
	 * Get all results for course and user
	 * 
	 * @return array
	 */	
	public function getCourseResultsForUserPresentation()
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT *".
			" FROM loc_user_results".
			" WHERE course_id = ".$ilDB->quote($this->course_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($this->user_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$objective_id = $row["objective_id"];
			$type = $row["type"];
			unset($row["objective_id"]);
			unset($row["type"]);
			$res[$objective_id][$type] = $row;			
		}
		
		return $res;
	}		
	
	public static function getObjectiveStatusForLP($a_user_id, array $a_objective_ids)
	{
		global $ilDB;
		
		// this method returns LP status codes!		
		include_once "Services/Tracking/classes/class.ilLPStatus.php";
		
		$res = array();
		
		$sql = "SELECT lor.objective_id, lor.user_id, lor.status, lor.is_final".
			" FROM loc_user_results lor".
			" JOIN crs_objectives cobj ON (cobj.objective_id = lor.objective_id)".	
			" WHERE ".$ilDB->in("lor.objective_id", $a_objective_ids, "", "integer").
			" AND lor.type = ".$ilDB->quote(self::TYPE_QUALIFIED, "integer").
			" AND lor.user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND cobj.active = ".$ilDB->quote(1, "integer");		
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{									
			switch($row["status"])
			{
				case self::STATUS_FAILED:					
					$status = ilLPStatus::LP_STATUS_FAILED_NUM;					
					break;
				
				case self::STATUS_COMPLETED:
					$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;				
					break;
				
				default:
					/*
					$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;					
					break;													 
					*/
					continue;
			}
			
			$res[$row["objective_id"]] = $status;						
		}				
		
		return $res;		
	}
	
	public static function getSummarizedObjectiveStatusForLP(array $a_objective_ids, $a_user_id = null)
	{
		global $ilDB;
		
		// this method returns LP status codes!		
		include_once "Services/Tracking/classes/class.ilLPStatus.php";
				
		$res = $tmp_completed = array();		
		
		$sql = "SELECT lor.objective_id, lor.user_id, lor.status, lor.is_final".
			" FROM loc_user_results lor".
			" JOIN crs_objectives cobj ON (cobj.objective_id = lor.objective_id)".	
			" WHERE ".$ilDB->in("lor.objective_id", $a_objective_ids, "", "integer").
			" AND lor.type = ".$ilDB->quote(self::TYPE_QUALIFIED, "integer").
			" AND cobj.active = ".$ilDB->quote(1, "integer");	
		if($a_user_id)
		{
			$sql .= " AND lor.user_id = ".$ilDB->quote($a_user_id, "integer");
		}		
		$set = $ilDB->query($sql);			
		while($row = $ilDB->fetchAssoc($set))
		{								
			$user_id = (int)$row["user_id"];
			$status = (int)$row["status"];
			
			// user did do something
			$res[$user_id] = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
			
			switch($status)
			{
				case self::STATUS_COMPLETED:
					$tmp_completed[$user_id]++;
					break;
				
				case self::STATUS_FAILED:
					if((bool)$row["is_final"])
					{
						// object is failed when at least 1 objective is failed without any tries left
						$res[$user_id] = ilLPStatus::LP_STATUS_FAILED_NUM;
					}
					break;															
			}			
		}	
		
		$all_nr = sizeof($a_objective_ids);
		foreach($tmp_completed as $user_id => $counter)
		{
			// if used as precondition object should be completed ASAP, status can be lost on subsequent tries
			if($counter == $all_nr)
			{
				$res[$user_id] = ilLPStatus::LP_STATUS_COMPLETED_NUM;
			}			
		}
		
		if($a_user_id)
		{
			// might return null!
			return $res[$a_user_id];
		}
		else
		{		
			return $res;		
		}
	}
	
	
	public static function hasResults($a_container_id, $a_user_id)
	{
		global $ilDB;
		
		$query = 'SELECT objective_id FROM loc_user_results '.
				'WHERE course_id = '.$ilDB->quote($a_container_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_user_id,'integer');
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}
}

?>