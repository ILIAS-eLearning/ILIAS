<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * (Course) Participation status
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesParticipationStatus
 */
class ilParticipationStatus
{
	static protected $instances = array();
	
	const STATUS_NOT_SET = 1;
	const STATUS_SUCCESSFUL = 2;
	const STATUS_ABSENT_EXCUSED = 3;
	const STATUS_ABSENT_NOT_EXCUSED = 4;
	
	const MODE_NON_REVIEWED = 1;
	const MODE_REVIEWED = 2;
	const MODE_CONTINUOUS = 3;
	
	const STATE_SET = 1;
	const STATE_REVIEW = 2;
	const STATE_FINALIZED = 3;
			
	/**
	 * Constructor
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */	
	protected function __construct(ilObjCourse $a_course)
	{
		$this->setCourse($a_course);				
	}
	
	/**
	 * Factory
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */
	public static function getInstance(ilObjCourse $a_course)
	{
		$crs_id = $a_course->getId();
		
		if(!array_key_exists($crs_id, self::$instances))
		{
			self::$instances[$crs_id] = new self($a_course);
		}
		
		return self::$instances[$crs_id];
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_course_ref_id
	 * @return self
	 */
	public static function getInstanceByRefId($a_course_ref_id)
	{			
		global $tree;
		
		if(array_key_exists($a_course_ref_id, self::$instances))
		{
			return self::$instances[$a_course_ref_id];
		}	
		
		if(ilObject::_lookupType($a_course_ref_id, true) != "crs" ||
			$tree->isDeleted($a_course_ref_id))
		{
			throw new ilException("ilParticipationStatus - needs course ref id");
		}
		
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_course_ref_id);
		
		return self::getInstance($course);
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourse()
	{
		return $this->course;
	}
	
	
	// 
	// status
	//
	
	/**
	 * Get status translation
	 * 
	 * @param int $a_status
	 * @return string
	 */
	public function statusToString($a_status)
	{
		$a_status = (int)$a_status;
		$all = $this->getValidStatus(true);
		if(array_key_exists($a_status, $all))
		{
			return $all[$a_status];
		}
	}
	
	/**
	 * Get all status 
	 * 
	 * @param bool $a_include_captions
	 * @return array
	 */
	public function getValidStatus($a_include_captions = false)
	{
		global $lng;
		
		if(!$a_include_captions)
		{
			return array(
				self::STATUS_NOT_SET
				,self::STATUS_SUCCESSFUL
				,self::STATUS_ABSENT_EXCUSED
				,self::STATUS_ABSENT_NOT_EXCUSED
			);
		}
		else
		{
			return array(
				self::STATUS_NOT_SET => $lng->txt("ptst_status_not_set")
				,self::STATUS_SUCCESSFUL => $lng->txt("ptst_status_successful")
				,self::STATUS_ABSENT_EXCUSED => $lng->txt("ptst_status_absent_excused")
				,self::STATUS_ABSENT_NOT_EXCUSED => $lng->txt("ptst_status_absent_not_excused")
			);
		}
	}
	
	/**
	 * Is given status valid?
	 * 
	 * @param int $a_status
	 * @return bool
	 */
	protected function isValidStatus($a_status)
	{
		return in_array($a_status, $this->getValidStatus());
	}
	
	/**
	 * Set user status
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 * @param int $a_status
	 * @return boolean
	 */
	public function setStatus($a_user_id, $a_status)
	{
		if($this->getProcessState() == self::STATE_FINALIZED)
		{
			throw new ilException("ilParticipationsStatus - trying to set status when already finalized");
		}
		
		$a_user_id = (int)$a_user_id;
		if($a_status !== null)
		{
			$a_status = (int)$a_status;		
		}
		if($a_user_id &&
			($a_status === null || $this->isValidStatus($a_status)))
		{
			$old = $this->getStatus($a_user_id, false);
			if($old !== $a_status)
			{								
				$this->updateDBUserData($a_user_id, $a_status, -1);				
				
				$this->setLPForStatus($a_user_id, $a_status);								
			
				// coming from LP => announce event
				if($this->getMode() == self::MODE_CONTINUOUS)
				{
					$this->raiseEvent("setStatusAndPoints", $a_user_id);			
				}
				// #35 - coming from GUI => update LP
				else
				{					
					$this->setLPForStatus($a_user_id, $a_status);			
				}
			}
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get user status
	 * 
	 * @param int $a_user_id
	 * @param bool $a_use_default
	 * @return int
	 */
	public function getStatus($a_user_id, $a_use_default = true)
	{
		$a_user_id = (int)$a_user_id;		
		if($a_user_id)
		{
			$data = $this->getDBUserData($a_user_id);
			if(is_array($data))
			{
				if(!$a_use_default || $data["status"])
				{
					if($data["status"] !== null)
					{
						$data["status"] = (int)$data["status"];
					}
					return $data["status"];
				}
			}
			if($a_use_default)
			{
				return self::STATUS_NOT_SET;
			}
		}
	}
	
	/**
	 * Get status of all course members
	 * 
	 * @return array
	 */
	public function getAllStatus()
	{
		$res = array();
		
		$all = $this->getDBUsersData();
		foreach($this->getCourse()->getMembersObject()->getMembers() as $user_id)
		{
			if(isset($all[$user_id]) && $all[$user_id]["status"])
			{
				$res[$user_id] = $all[$user_id]["status"];
			}
			else
			{
				$res[$user_id] = self::STATUS_NOT_SET;
			}						
		}
		
		return $res;
	}
	
	/**
	 * Check if all members of course do not have STATUS_NOT_SET
	 * 
	 * @return bool
	 */
	public function allStatusSet()
	{				
		foreach($this->getAllStatus() as $status)
		{
			if($status == self::STATUS_NOT_SET)
			{
				return false;
			}
		}
		
		return true;
	}
	
	
	//
	// learning progress
	// 
	
	/**
	 * Synchronize LP with statzs
	 * 
	 * @param int $a_user_id
	 * @param int $a_status
	 */
	protected function setLPForStatus($a_user_id, $a_status)
	{
		require_once 'Services/Tracking/classes/class.ilLPStatus.php';
		
		$completed = false;
		switch($a_status)
		{
			case self::STATUS_NOT_SET:
				$lp_status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
				break;
			
			case self::STATUS_SUCCESSFUL:
				$lp_status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
				$completed = true;
				break;
			
			case self::STATUS_ABSENT_EXCUSED:
			case self::STATUS_ABSENT_NOT_EXCUSED:
				$lp_status = ilLPStatus::LP_STATUS_FAILED_NUM;
				break;			
		}
		
		$comment = "lp_status_".$lp_status;
		
		
		// see ilLearningProgressBaseGUI::__updateUser()
		
		$changed = false;
		
		require_once 'Services/Tracking/classes/class.ilLPMarks.php';
		$marks = new ilLPMarks($this->getCourse()->getId(), $a_user_id);
		
		if($marks->getCompleted() != $completed)
		{
			$marks->setCompleted($completed);
			$changed = true;
		}
						
		if($marks->getComment() != $comment)
		{
			$marks->setComment($comment);
			$changed = true;
		}				

		if($changed)
		{
			$marks->update();
			
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_updateStatus($this->getCourse()->getId(), $a_user_id);			
		}
	}
	
	
	// 
	// credit points
	// 
	
	/**
	 * Is credit points amount valid?
	 * 
	 * @param int $a_points
	 * @return bool
	 */
	protected function isValidCreditPoints($a_points)
	{
		return ($a_points >= 0 && $a_points <= $this->getMaxCreditPoints());
	}
	
	/**
	 * Set user credit points
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 * @param int $a_points
	 * @return boolean
	 */
	public function setCreditPoints($a_user_id, $a_points)
	{
		if($this->getProcessState() == self::STATE_FINALIZED)
		{
			throw new ilException("ilParticipationsStatus - trying to set credit points when already finalized");
		}
		
		$a_user_id = (int)$a_user_id;
		if($a_points !== null)
		{
			$a_points = (int)$a_points;
		}
		if($a_user_id &&
			($a_points === null || $this->isValidCreditPoints($a_points)))
		{
			$old = $this->getCreditPoints($a_user_id, false);
			if($old !== $a_points)
			{
				$this->updateDBUserData($a_user_id, -1, $a_points);
				
				if($this->getMode() == self::MODE_CONTINUOUS)
				{
					$this->raiseEvent("setStatusAndPoints", $a_user_id);			
				}		
			}
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get user status
	 * 
	 * @param int $a_user_id
	 * @param bool $a_use_default
	 * @return int
	 */
	public function getCreditPoints($a_user_id, $a_use_default = true)
	{
		$a_user_id = (int)$a_user_id;		
		if($a_user_id)
		{			
			if($a_use_default && $this->getStatus($a_user_id) != self::STATUS_SUCCESSFUL)
			{
				return 0;
			}
			
			$data = $this->getDBUserData($a_user_id);
			if(is_array($data))
			{
				if(!$a_use_default || $data["cpoints"])
				{
					if($data["cpoints"] !== null)
					{
						$data["cpoints"] = (int)$data["cpoints"];
					}
					return $data["cpoints"];				
				}
			}
			
			if($a_use_default)
			{
				return $this->getMaxCreditPoints();
			}
		}
	}
	
	/**
	 * Get credit points of all course members
	 * 
	 * @return array
	 */
	public function getAllCreditPoints()
	{
		$res = array();
		
		$max = $this->getMaxCreditPoints();
		
		$all = $this->getDBUsersData();
		foreach($this->getCourse()->getMembersObject()->getMembers() as $user_id)
		{					
			if(!isset($all[$user_id]) || $all[$user_id]["status"] != self::STATUS_SUCCESSFUL)
			{
				$res[$user_id] = 0;
			}
			else if($all[$user_id]["cpoints"])
			{
				$res[$user_id] = (int)$all[$user_id]["cpoints"];				
			}
			else
			{
				$res[$user_id] = $max;						
			}
		}
		
		return $res;
	}
	
	
	//
	// status/credit points
	//
	
	/**
	 * Get status and credit points for user
	 * 
	 * @param int $a_user_id
	 * @return array (status, points)
	 */
	public function getStatusAndPoints($a_user_id)
	{
		$a_user_id = (int)$a_user_id;		
		if($a_user_id)
		{
			$status = self::STATUS_NOT_SET;
			$points = 0;
			
			$data = $this->getDBUserData($a_user_id);
			if(is_array($data))
			{
				if($data["status"])
				{
					$status = $data["status"];
				}
				if($data["status"] == self::STATUS_SUCCESSFUL)
				{
					if($data["cpoints"])
					{
						$points = (int)$data["cpoints"];
					}
					else
					{
						$points = $this->getMaxCreditPoints();
					}
				}								
			}
			
			return array(
				"status" => $status
				,"points" => $points
			);
		}		
	}
			
	/**
	 * Get status and credit points of all course members
	 * 
	 * @param bool $a_use_defaults
	 * @return array
	 */
	public function getAllStatusAndPoints($a_use_defaults = true)
	{
		$res = array();
		
		$max = $this->getMaxCreditPoints();
		
		$all = $this->getDBUsersData();
		foreach($this->getCourse()->getMembersObject()->getMembers() as $user_id)
		{	
			$status = $points = null;
			
			if(isset($all[$user_id]) && $all[$user_id]["status"] !== null)
			{
				$status = $all[$user_id]["status"];
			}
			else if($a_use_defaults)
			{
				$status = self::STATUS_NOT_SET;
			}	
						
			if($a_use_defaults && (!isset($all[$user_id]) || $all[$user_id]["status"] != self::STATUS_SUCCESSFUL))
			{
				$points = 0;
			}
			else if($all[$user_id]["cpoints"] !== null)
			{
				$points = (int)$all[$user_id]["cpoints"];				
			}
			else if($a_use_defaults)
			{
				$points = $max;						
			}
			
			$res[$user_id] = array(
				"status" => $status
				,"points" => $points
			);
		}
		
		return $res;
	}
	
	
	//
	// state
	//
	
	/**
	 * Get course process state
	 * 
	 * @return int
	 */
	public function getProcessState()
	{
		if($this->getMode() != self::MODE_CONTINUOUS)
		{			
			$data = $this->getDBCourseData();
			if(is_array($data) && $data["state"])
			{
				return $data["state"];
			}
		}
		
		return self::STATE_SET;
	}		
	
	/**
	 * Finalize course process state
	 * 
	 * @throws ilException
	 * @return bool
	 */
	public function finalizeProcessState()
	{
		$current = $this->getProcessState();
		if($current == self::STATE_FINALIZED)
		{
			throw new ilException("ilParticipationStatus - trying to finalize final state");
		}
		
		$new = null;
		switch($this->getMode())
		{
			case self::MODE_NON_REVIEWED:
				if($current == self::STATE_SET)
				{
					$new = self::STATE_FINALIZED;
				}
				break;
			
			case self::MODE_REVIEWED:
				if($current == self::STATE_SET)
				{
					$new = self::STATE_REVIEW;
				}
				else if($current == self::STATE_REVIEW)
				{
					$new = self::STATE_FINALIZED;
				}
				break;
						
			case self::MODE_CONTINUOUS:
				throw new ilException("ilParticipationStatus - trying to finalize in continuous mode");
				break;						
		}
		
		if($new)
		{	
			$this->updateDBCourseData($new, -1);
						
			if($new == self::STATE_FINALIZED)
			{
				foreach($this->getCourse()->getMembersObject()->getMembers() as $user_id)
				{
					$this->raiseEvent("setStatusAndPoints", $user_id);
				}			
			}
			
			return true;
		}		
		
		return false;
	}
	
	
	//
	// attendance list
	//
	
	/**
	 * Init file system storage
	 * 
	 * @param string $a_return_path;
	 * @return string 
	 */
	protected function initStorage($a_return_path = true)
	{		
		include_once "Services/ParticipationStatus/classes/class.ilFSParticipationStatus.php";
		$storage = new ilFSParticipationStatus($this->getCourse()->getId());
		$storage->create();
		
		if($a_return_path)
		{
			return $storage->getAbsolutePath()."/";
		}
		else
		{
			return $storage;
		}
	}
	
	/**
	 * Upload attendance list file
	 * 
	 * @param array $a_upload
	 */
	public function uploadAttendanceList(array $a_upload)
	{
		$path = $this->initStorage();
		
		$clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $a_upload["name"]);
		$new_file = $path.$clean_name;
		
		if(@move_uploaded_file($a_upload["tmp_name"], $new_file))
		{
			chmod($new_file, 0770);
			
			$this->setAttendanceList(basename($new_file));
			
			return true;
		}
		return false;

	}
	
	/**
	 * Set (path to) attendance list file
	 * 
	 * @param string $a_path
	 */
	public function setAttendanceList($a_path)
	{
		return $this->updateDBCourseData(null, $a_path);
	}
	
	/**
	 * Get (path to) attendance list file
	 * 
	 * @return string 
	 */
	public function getAttendanceList()
	{
		$data = $this->getDBCourseData();
		if(is_array($data) && $data["alist"])
		{
			$path = $this->initStorage();
			return $path.$data["alist"];
		}
	}
	
	
	/**
	 * Delete attendance list
	 * 
	 * @return bool
	 */
	public function deleteAttendanceList()
	{
		$storage = $this->initStorage(false);
		$storage->delete();
		
		$this->updateDBCourseData(-1, null);
	}
	
	
	//
	// course helper
	// 
	
	/**
	 * Get course participation status mode
	 * 
	 * @return int
	 */
	public function getMode()
	{
		require_once "Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php";
		$helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		return $helper->getParticipationStatusMode();
	}
	
	/**
	 * Get course max credit points
	 * 
	 * @return int
	 */
	public function getMaxCreditPoints()
	{
		require_once "Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php";
		$helper = ilParticipationStatusHelper::getInstance($this->getCourse());
		return $helper->getMaxCreditPoints();	
	}
	
	
	//
	// events
	//
	
	/**
	 * Raise event
	 * 	 
	 * @param string $a_event
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	protected function raiseEvent($a_event, $a_user_id = null)
	{
		global $ilAppEventHandler;
		
		$params = array(
			"crs_obj_id" => $this->getCourse()->getId()
		);
		if($a_user_id)
		{						
			$params["user_id"] = $a_user_id;			
		}
		
		$ilAppEventHandler->raise("Services/ParticipationsStatus", $a_event, $params);
	}
	
	
	//
	// DB (CRUD) user
	// 
	
	/**
	 * Get user status data
	 * 
	 * @param int $a_user_id
	 * @return array
	 */
	protected function getDBUserData($a_user_id)
	{
		global $ilDB;
		
		$sql = "SELECT status, cpoints".
			" FROM crs_pstatus_usr".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND crs_id = ".$ilDB->quote($this->getCourse()->getId(), "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			return $ilDB->fetchAssoc($set);
		}
	}
	
	/**
	 * Get all users status data
	 * 
	 * @return array
	 */
	protected function getDBUsersData()
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT user_id, status, cpoints".
			" FROM crs_pstatus_usr".
			" WHERE crs_id = ".$ilDB->quote($this->getCourse()->getId(), "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["user_id"]] = $row;
		}
		
		return $res;
	}
	
	/**
	 * Update user status data
	 * 
	 * @param int $a_user_id
	 * @param int $a_status
	 * @param int $a_credit_points
	 * @return bool
	 */
	protected function updateDBUserData($a_user_id, $a_status, $a_credit_points)
	{
		global $ilDB, $ilUser;
			
		$fields = array();		
		if($a_status !== -1)
		{
			$fields["status"] = array("integer", $a_status);		
		}
		if($a_credit_points !== -1)
		{
			$fields["cpoints"] = array("integer", $a_credit_points);	
		}
		$fields["changed_by"] = array("integer", $ilUser->getId());
		$fields["changed_on"] = array("integer", time());

		$primary = array(
			"user_id" => array("integer", $a_user_id)
			,"crs_id" => array("integer", $this->getCourse()->getId())
		);

		$old = $this->getDBUserData($a_user_id);
		if($old)
		{				
			$ilDB->update("crs_pstatus_usr", $fields, $primary);
		}
		else
		{				
			$ilDB->insert("crs_pstatus_usr", array_merge($fields, $primary));
		}
		
		return true;
	}	
		
	/**
	 * Delete user status 
	 * 
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	protected function deleteDBUserStatus($a_user_id)
	{
		global $ilDB;
		
		$old = $this->getDBUserData($a_user_id);
		if($old)		
		{
			$sql = "DELETE FROM crs_pstatus_usr".
				" WHERE crs_id = ".$ilDB->quote($this->getCourse()->getId(), "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer");
			$ilDB->manipulate($sql);
			
			require_once "Services/Tracking/classes/class.ilLPMarks.php";
			ilLPMarks::_deleteForUsers($this->getCourse()->getId(), array($a_user_id));
			
			$this->raiseEvent("deleteStatus", $a_user_id);			
		}					
	}
	
	/**
	 * Get change data
	 * 
	 * @param array $a_user_ids
	 * @return array
	 */
	public function getDBChangeData(array $a_user_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT user_id, changed_by, changed_on".
			" FROM crs_pstatus_usr".
			" WHERE crs_id = ".$ilDB->quote($this->getCourse()->getId(), "integer").
			" AND ".$ilDB->in("user_id", $a_user_ids, "", "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["user_id"]] = $row;							
		}
		
		return $res;
	}
	
	
	//
	// DB (CRUD) course
	// 
	
	/**
	 * Get course status data
	 * 
	 * @return array
	 */
	protected function getDBCourseData()
	{		
		global $ilDB;
		
		$sql = "SELECT state,alist".
			" FROM crs_pstatus_crs".
			" WHERE crs_id = ".$ilDB->quote($this->getCourse()->getId(), "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			return $ilDB->fetchAssoc($set);
		}
	}
	
	/**
	 * Update course status data
	 * 
	 * @param int $a_state
	 * @param string $a_attendance_list
	 * @return boolean
	 */
	protected function updateDBCourseData($a_state, $a_attendance_list)
	{
		global $ilDB;
	
		$a_state = (int)$a_state;
		$a_attendance_list = (string)$a_attendance_list;
				
		if($a_state || $a_attendance_list)	
		{		
			$fields = array();
			if($a_state !== -1)
			{
				$fields["state"] = array("integer", $a_state);
			}
			if($a_attendance_list !== "-1") // #36
			{
				$fields["alist"] = array("text", $a_attendance_list);
			}
			
			$primary = array(				
				"crs_id" => array("integer", $this->getCourse()->getId())
			);
			
			$old = $this->getDBCourseData();
			if($old)
			{
				$ilDB->update("crs_pstatus_crs", $fields, $primary);
			}
			else
			{				
				$ilDB->insert("crs_pstatus_crs", array_merge($fields, $primary));
			}
			
			return true;
		}
		
		return false;		
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
		
		$sql = "DELETE FROM crs_pstatus_usr".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer");
		$ilDB->manipulate($sql);
		
		$sql = "DELETE FROM crs_pstatus_crs".
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
		
		$sql = "DELETE FROM crs_pstatus_usr".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer");
		$ilDB->manipulate($sql);		
	}
	
	
	//
	// ORG UNIT (this is also in ilCourseBookingHelper)
	//
	
	/**
	 * Get org unit titles
	 * 
	 * @param array $a_ref_ids
	 * @return array
	 */
	public static function getOrgUnitTitles(array $a_ref_ids)
	{
		global $ilDB;
		
		$titles = array();
		
		$sql = "SELECT oref.ref_id,od.title FROM object_data od".
			" JOIN object_reference oref ON (oref.obj_id = od.obj_id)".
			" WHERE ".$ilDB->in("oref.ref_id", $a_ref_ids, "", "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$titles[$row["ref_id"]] = $row["title"];
		}		
	
		return $titles;
	}
			
	/**
	 * Get org unit data for users
	 * 
	 * @param array $a_user_ids
	 * @return array
	 */
	public static function getUsersOrgUnitData(array $a_user_ids)
	{				
		$res = array();				
		
		require_once "Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php";
		$ou_tree = ilObjOrgUnitTree::_getInstance();		
		
		// get node levels
		$levels = array();
		$level = 0;
		while($children = $ou_tree->getAllOrgunitsOnLevelX(++$level))
		{
			foreach($children as $child_id)
			{
				$levels[$child_id] = $level;
			}
		}
		
		$titles = self::getOrgUnitTitles(array_keys($levels));
		
		// :TODO: way too slow?
		foreach($a_user_ids as $user_id)
		{		
			$ou = null;
			$ou_txt = "";
			
			$ou_ids = $ou_tree->getOrgUnitOfUser($user_id);
			if(is_array($ou_ids))
			{
				$ou = $ou_ids;
				$ou_txt = array();						
				foreach($ou_ids as $ou_id)
				{
					$ou_level = $levels[$ou_id];
					$ou_txt[$ou_level] = $titles[$ou_id];					
				}
				krsort($ou_txt);
				$ou_txt = implode(", ", $ou_txt);			
			}
			
			$res[$user_id] = array($ou, $ou_txt);
		}
		
		return $res;
	}
	
	
	// 
	// GUI
	//	
		
	/**
	 * Get status and credit points of all course members for table GUI
	 * 
	 * @return array
	 */
	public function getCourseTableData()
	{
		global $ilDB;
		
		$res = array();
		
		$user_ids = array();		
		foreach($this->getAllStatusAndPoints(false) as $user_id => $item)
		{
			$user_ids[] = $user_id;
			$res[$user_id] = $item;
		}
		
		$orgu = $this->getUsersOrgUnitData($user_ids);
		
		// change data
		foreach($this->getDBChangeData($user_ids) as $user_id => $item)
		{
			$user_ids[] = $item["changed_by"];			
			$res[$item["user_id"]]["changed_by"] = $item["changed_by"];
			$res[$item["user_id"]]["changed_on"] = $item["changed_on"];
		}		
		
		// gather user names
		$users = array();
		$sql = "SELECT usr_id, firstname, lastname, login".
			" FROM usr_data".
			" WHERE ".$ilDB->in("usr_id", array_unique($user_ids), "", "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$users[$row["usr_id"]] = $row;
		}
		
		// merge user names, org unit, change data 
		foreach($res as $user_id => $item)
		{
			$res[$user_id]["user_id"] = $user_id;
			
			$res[$user_id]["login"] = $users[$user_id]["login"];
			$res[$user_id]["firstname"] = $users[$user_id]["firstname"];
			$res[$user_id]["lastname"] = $users[$user_id]["lastname"];
			
			$res[$user_id]["org_unit"] = $orgu[$user_id][0];
			$res[$user_id]["org_unit_txt"] = $orgu[$user_id][1];
			
			$res[$user_id]["changed_by_txt"] = $users[$item["changed_by"]]["login"];			
		}
		
		return array_values($res);		
	}
}