<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course booking 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBooking
{	
	const STATUS_BOOKED = 1;
	const STATUS_WAITING = 2;
	const STATUS_CANCELLED_WITH_COSTS = 3;
	const STATUS_CANCELLED_WITHOUT_COSTS = 4;
	
	
	//
	// status
	//
	
	/**
	 * Is given status valid?
	 * 
	 * @param int $a_status
	 * @return bool
	 */
	protected static function isValidStatus($a_status)
	{
		return in_array($a_status, array(
			self::STATUS_BOOKED
			,self::STATUS_WAITING
			,self::STATUS_CANCELLED_WITH_COSTS
			,self::STATUS_CANCELLED_WITHOUT_COSTS
		));		
	}
		
	
	// 
	// crud
	// 
	
	/**
	 * Get user status (without changed info)
	 * 
	 * @param int $a_course_obj_id
	 * @return int
	 */
	public static function getUserStatus($a_course_obj_id, $a_user_id)
	{
		//gev-patch start
		global $ilDB, $ilLog;
		$ilLog->write("Search state in crs_book");
		//gev-patch end

		$sql = "SELECT status".
			" FROM crs_book".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$res = $ilDB->fetchAssoc($set);
			return $res["status"];
		}
		$ilLog->write("No state found");
	}
	
	/**
	 * Get user status (with changed info)
	 * 
	 * @param int $a_course_obj_id
	 * @return array
	 */
	public static function getUserData($a_course_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$sql = "SELECT status, status_changed_by, status_changed_on".
			" FROM crs_book".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$res = $ilDB->fetchAssoc($set);
			return $res;
		}
	}
	
	/**
	 * Set user status 
	 * 
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 * @param int $a_status
	 * @return bool
	 */
	public static function setUserStatus($a_course_obj_id, $a_user_id, $a_status)
	{
		//gev-patch start ilLog
		global $ilDB, $ilUser, $ilLog;
		
		$ilLog->write("Start set user state. User: ". $a_user_id." crs: ".$a_course_obj_id." state: ".$a_status);
		if(!self::isValidStatus($a_status))
		{
			$ilLog->write("state: ".$a_status. " is not valid");
			return false;
		}
		
		$fields = array(
			"status" => array("integer", $a_status)
			,"status_changed_by" => array("integer", $ilUser->getId())
			,"status_changed_on" => array("integer", time())
		);
		
		$old = self::getUserStatus($a_course_obj_id, $a_user_id);
		if(self::isValidStatus($old))
		{
			$ilLog->write("Update user state");
			if($old == $a_status)
			{
				return true;
			}
			
			$primary = array(
				"crs_id" => array("integer", $a_course_obj_id)
				,"user_id" => array("integer", $a_user_id)
			);						
			$ilDB->update("crs_book", $fields, $primary);
			$ilLog->write("Update user state finished");
		}
		else
		{
			$ilLog->write("Insert user state");
			$fields["crs_id"] = array("integer", $a_course_obj_id);
			$fields["user_id"] = array("integer", $a_user_id);
			
			$ilDB->insert("crs_book", $fields);
			$ilLog->write("Insert user state finished");
		}
				
		self::raiseEvent("setStatus", $a_course_obj_id, $a_user_id, $old, $a_status);
		
		return true;
	}
	
	/**
	 * Raise event
	 * 	 
	 * @param string $a_event
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	protected static function raiseEvent($a_event, $a_course_obj_id = null, $a_user_id = null, $old_status = null, $new_status = null)
	{
		global $ilAppEventHandler;
		
		$params = null;
		if($a_course_obj_id || $a_user_id)
		{
			$params = array();
			if($a_course_obj_id)
			{
				$params["crs_obj_id"] = $a_course_obj_id;
			}
			if($a_user_id)
			{
				$params["user_id"] = $a_user_id;
			}
			$params["old_status"] = $old_status;
			$params["new_status"] = $new_status;
		}
		
		$ilAppEventHandler->raise("Services/CourseBooking", $a_event, $params);
	}
	
	/**
	 * Delete user status 
	 * 
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	public static function deleteUserStatus($a_course_obj_id, $a_user_id)
	{
		global $ilDB;
		
		// :TODO: obsolete?
		
		$old = self::getUserStatus($a_course_obj_id, $a_user_id);
		if(self::isValidStatus($old))		
		{
			$sql = "DELETE FROM crs_book".
				" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer");
			$ilDB->manipulate($sql);
			
			self::raiseEvent("deleteStatus", $a_course_obj_id, $a_user_id, $old, null);			
		}					
	}
	
	
	// 
	// destructor
	//		
	
	/**
	 * Delete all course entries (all users!)
	 * 
	 * @param int $a_course_obj_id
	 */
	public static function deleteByCourseId($a_course_obj_id)
	{
		global $ilDB;
		
		// :TODO: unroll to raise events?
		
		$sql = "DELETE FROM crs_book".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer");
		$ilDB->manipulate($sql);
	}
	
	/**
	 * Delete all user entries (all courses!)
	 * 
	 * @param int $a_user_id
	 */
	public static function deleteByUserId($a_user_id)
	{
		global $ilDB;
		
		// :TODO: unroll to raise events?
		
		$sql = "DELETE FROM crs_book".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer");
		$ilDB->manipulate($sql);		
	}
			
	
	// 
	// info
	// 
	
	/**
	 * Validate status (1-n)
	 * 
	 * @param int|array $a_status
	 * @return array
	 */
	protected static function validateStatus($a_status)
	{		
		if(!is_array($a_status))
		{
			$a_status = array($a_status);
		}
		
		foreach($a_status as $idx => $status)
		{
			if(!self::isValidStatus($status))
			{
				unset($a_status[$idx]);
			}
		}
		
		if(sizeof($a_status))
		{		
			return $a_status;
		} 
	}
	
	/**
	 * Get users of course by status (1-n)
	 * 
	 * @param int $a_course_obj_id
	 * @param int|array $a_status
	 * @param bool $a_return_status
	 * @return array
	 */
	public static function getUsersByStatus($a_course_obj_id, $a_status, $a_return_status = false)
	{
		global $ilDB;
		
		$status = self::validateStatus($a_status);
		if(sizeof($status))
		{
			$res = array();
			
			$sql = "SELECT user_id, status".
				" FROM crs_book".
				" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
				" AND ".$ilDB->in("status", $status, "", "integer");
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{
				if($a_return_status)
				{
					$res[$row["user_id"]] = $row["status"];
				}
				else
				{
					$res[] = $row["user_id"];
				}
			}
			
			return $res;
		}
	}
	
	/**
	 * Get courses of user by status (1-n)
	 * 
	 * @param int $a_user_id
	 * @param int|array $a_status
	 * @return array
	 */
	public static function getCoursesByStatus($a_user_id, $a_status)
	{
		global $ilDB;
		
		$status = self::validateStatus($a_status);
		if(sizeof($status))
		{
			$res = array();
			
			$sql = "SELECT crs_id".
				" FROM crs_book".
				" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ".$ilDB->in("status", $status, "", "integer");
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{
				$res[] = $row["crs_id"];
			}
			
			return $res;
		}
	}
	
	/**
	 * Get complete course booking data for table GUI
	 * 
	 * @param int $a_course_obj_id
	 * @param bool $a_show_cancellations
	 * @param int $a_offset
	 * @param int $a_limit
	 * @return array
	 */
	public static function getCourseTableData($a_course_obj_id, $a_offset, $a_limit, $a_show_cancellations = false)
	{
		global $ilDB;
		
		$res = array();
		
		$user_ids = array();
		
		if(!$a_show_cancellations)
		{
			$status = array(self::STATUS_BOOKED, self::STATUS_WAITING);
		}
		else
		{
			$status = array(self::STATUS_CANCELLED_WITHOUT_COSTS, self::STATUS_CANCELLED_WITH_COSTS);
		}
		
		$sql = "SELECT  usr.firstname AS firstname, usr.lastname AS lastname, usr.login AS login, change_usr.login AS stcblogin,"
					." crb.status, crb.status_changed_on,crb.crs_id,crb.user_id,crb.status_changed_by,"
					." GROUP_CONCAT(ou_title.obj_id SEPARATOR '#|#') AS oguid, GROUP_CONCAT(ou_title.title SEPARATOR '#|#') AS ogutitle"
					." FROM crs_book crb"
					." JOIN usr_data usr ON usr.usr_id = crb.user_id"
					." JOIN usr_data change_usr ON change_usr.usr_id = crb.status_changed_by"
					." JOIN rbac_ua ua ON ua.usr_id = crb.user_id"
					." LEFT JOIN object_data role_data ON role_data.obj_id = ua.rol_id AND ( role_data.title LIKE 'il_orgu_superior_%' OR role_data.title LIKE 'il_orgu_employee_%')"
					." LEFT JOIN object_reference ou_ref ON ou_ref.ref_id = SUBSTRING(role_data.title, 18)"
					." LEFT JOIN object_data ou_title ON ou_title.obj_id = ou_ref.obj_id"
					." WHERE crb.crs_id = ".$ilDB->quote($a_course_obj_id, "integer")." AND ".$ilDB->in("crb.status", $status, "", "integer").""
					." GROUP BY usr.firstname, usr.lastname, usr.login, change_usr.login,"
						." crb.status, crb.status_changed_on,crb.crs_id,crb.user_id,crb.status_changed_by";
		
		if($a_limit != 0) {
			if($a_offset === null) {
				$a_offset = 0;
			}

			$sql .= " LIMIT ".$a_offset.", ".$a_limit."";
		}

		$res = array();
		$arrIndex = 0;
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$arrIndex]["crs_id"] = $row["crs_id"];
			$res[$arrIndex]["user_id"] = $row["user_id"];
			$res[$arrIndex]["status"] = $row["status"];
			$res[$arrIndex]["status_changed_by"] = $row["status_changed_by"];
			$res[$arrIndex]["status_changed_on"] = $row["status_changed_on"];
			

			$res[$arrIndex]["firstname"] = $row["firstname"];
			$res[$arrIndex]["lastname"] = $row["lastname"];
			$res[$arrIndex]["login"] = $row["login"];
			
			$res[$arrIndex]["org_unit"] = explode("#|#",$row["oguid"]);
			
			$title = explode("#|#", $row["ogutitle"]);
			sort($title);
			$res[$arrIndex]["org_unit_txt"] = implode(", ",$title);
			
			$res[$arrIndex]["status_changed_by_txt"] = $row["stcblogin"];

			$arrIndex++;
		}
		
		return $res;
	}
}