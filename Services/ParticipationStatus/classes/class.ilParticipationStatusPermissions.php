<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course participations status permissions
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilParticipationStatusPermissions
{
	protected $course_id; // [int]
	protected $course; // [ilObjCourse]
	protected $user_id; // [int]
	protected $pstatus; // [ilParticipationStatus]
	
	static protected $instances = array();
	
	/**
	 * Constructor
	 * 
	 * @param int $a_course_ref_id
	 * @param int $a_user_id
	 * @return self
	 */
	protected function __construct($a_course_ref_id, $a_user_id = null)
	{	
		$this->setCourseId($a_course_ref_id);
		$this->setUserId($a_user_id);
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_course_ref_id
	 * @param int $a_user_id
	 * @return self
	 */
	public static function getInstanceByRefId($a_course_ref_id, $a_user_id = null)
	{				
		global $tree;
		
		if(!array_key_exists($a_course_ref_id, self::$instances))
		{
			if(ilObject::_lookupType($a_course_ref_id, true) != "crs" ||
				$tree->isDeleted($a_course_ref_id))
			{
				throw new ilException("ilParticipationStatusPermissions - needs course ref id");
			}
			
			self::$instances[$a_course_ref_id] = new self($a_course_ref_id, $a_user_id);
		}
		else
		{
			self::$instances[$a_course_ref_id]->setUserId($a_user_id);
		}
		
		return self::$instances[$a_course_ref_id];
	}	
	
	/**
	 * Factory
	 * 
	 * @param ilObjCourse $a_course
	 * @param int $a_user_id
	 * @return self
	 */
	public static function getInstance(ilObjCourse $a_course, $a_user_id = null)
	{				
		$crs_ref_id = $a_course->getRefId();
		
		if(array_key_exists($crs_ref_id, self::$instances))
		{
			self::$instances[$crs_ref_id]->setUserId($a_user_id);			
		}
		else
		{
			self::$instances[$crs_ref_id] = new self($crs_ref_id, $a_user_id);
		}		
		
		self::$instances[$crs_ref_id]->setCourse($a_course);
		return self::$instances[$crs_ref_id];
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course id
	 * 
	 * @param int $a_course_ref_id
	 */
	protected function setCourseId($a_course_ref_id)
	{
		$this->course_ref_id = (int)$a_course_ref_id;
	}
	
	/**
	 * Get course id
	 * 
	 * @return int
	 */	
	protected function getCourseId()
	{
		return $this->course_ref_id;
	}
	
	/**
	 * Set course 
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse($a_course)
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
	
	/**
	 * Set user id
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 */
	protected function setUserId($a_user_id)
	{
		global $ilUser;
		
		if(!(int)$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		
		if($a_user_id == ANONYMOUS_USER_ID)
		{
			throw new ilException("ilParticipationStatusPermissions - cannot handle anonymous user");
		}
		
		$this->user_id = (int)$a_user_id;
	}
	
	/**
	 * Get user id
	 * 
	 * @return int
	 */	
	protected function getUserId()
	{
		return $this->user_id;
	}
	
	/**
	 * Init participation status instance
	 * 
	 * @return ilParticipationStatus
	 */
	protected function initParticipationStatus()
	{
		if(!$this->pstatus instanceof ilParticipationStatus)
		{
			require_once "Services/ParticipationStatus/classes/class.ilParticipationStatus.php";
			
			$crs = $this->getCourse();
			if($crs)
			{
				$this->pstatus = ilParticipationStatus::getInstance($crs);
			}
			else
			{
				$this->pstatus = ilParticipationStatus::getInstanceByRefId($this->getCourseId());
			}			
		}
		return $this->pstatus;
	}
	
	
	//
	// permissions (view/set/review)
	// 
	
	/**
	 * Is user allowed to view course participation status?
	 * 
	 * @return bool
	 */
	public function viewParticipationStatus()
	{
		global $ilAccess;
		
		return $ilAccess->checkAccessOfUser($this->getUserId(), "view_participation_status", "", $this->getCourse()->getRefId());	
	}

	/**
	 * Is user allowed to set participation status?
	 * 
	 * @return bool
	 */
	public function setParticipationStatus()
	{
		global $ilAccess;
					
		$status = $this->initParticipationStatus();
		
		return ($status->getProcessState() == ilParticipationStatus::STATE_SET &&
			$ilAccess->checkAccessOfUser($this->getUserId(), "set_participation_status", "", $this->getCourse()->getRefId()));
	}

	/**
	 * Is user allowed to review participation status?
	 * 
	 * @return bool
	 */
	public function reviewParticipationStatus()
	{
		global $ilAccess;
			
		$status = $this->initParticipationStatus();
		
		return ($status->getProcessState() == ilParticipationStatus::STATE_REVIEW &&
			$ilAccess->checkAccessOfUser($this->getUserId(), "review_participation_status", "", $this->getCourse()->getRefId()));
	}	
}
