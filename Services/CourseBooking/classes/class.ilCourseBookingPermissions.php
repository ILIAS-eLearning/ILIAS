<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course booking permissions
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBookingPermissions
{
	protected $course_ref_id; // [int]
	protected $user_id; // [int]
	protected $course; // [ilObjCourse]
	
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
				throw new ilException("ilCourseBookingPermissions - needs course ref id");
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
			throw new ilException("ilCourseBookingPermissions - cannot handle anonymous user");
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
	
	
	//
	// permissions (view/book/cancel)
	// 
	
	/**
	 * Is user allowed to view own bookings?
	 * 
	 * @return bool
	 */
	public function viewOwnBookings()
	{
		return true;
	}
	
	/**
	 * Is user allowed to view other bookings?
	 * 
	 * @return bool
	 */
	public function viewOtherBookings()
	{
		global $ilAccess;
		
		return $ilAccess->checkAccessOfUser($this->getUserId(), "view_bookings", "", $this->getCourseId());
	}
	
	/**
	 * Is user allowed to view bookings of given user?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function viewBookingsOfUser($a_user_id)
	{
		$a_user_id = (int)$a_user_id;
		
		if($a_user_id)
		{
			if($this->getUserId() == $a_user_id)
			{
				return $this->viewOwnBookings();
			}
			
			if($this->viewOtherBookings())
			{
				return true;
			}
			
			// check org units (view_employee_bookings[_recursive])
			
			return false;			
		}				
	}
	
	/**
	 * Is user allowed to book course for himself?
	 * 
	 * @return bool
	 */
	public function bookCourseForSelf()
	{
		global $ilAccess;
		
		// ILIAS-Standard ?
		
		include_once "Services/CourseBooking/classes/class.ilCourseBookingHelper.php";		
		$crs = $this->getCourse();		
		if(!$crs instanceof ilObjCourse)
		{		
			$helper = ilCourseBookingHelper::getInstanceByRefId($this->getCourseId());
		}
		else
		{
			$helper = ilCourseBookingHelper::getInstance($crs);
		}
		
		// see ilObjCourseAccess::_checkAccess()
		
		return ($helper->isBookable() &&
			$ilAccess->checkAccessOfUser($this->getUserId(), "join", "", $this->getCourseId()));
	}
	
	/**
	 * Is user allowed to book course for others?
	 * 
	 * @return bool
	 */
	public function bookCourseForOthers()
	{
		global $ilAccess;
		
		
		return $ilAccess->checkAccessOfUser($this->getUserId(), "book_users", "", $this->getCourseId());
	}
	
	/**
	 * Is user allowed to book course for given user?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function bookCourseForUser($a_user_id)
	{
		$a_user_id = (int)$a_user_id;
		
		if($a_user_id)
		{
			if($this->getUserId() == $a_user_id)
			{
				return $this->bookCourseForSelf();
			}
			
			include_once "Services/CourseBooking/classes/class.ilCourseBookingHelper.php";		
			$crs = $this->getCourse();		
			if(!$crs instanceof ilObjCourse)
			{		
				$helper = ilCourseBookingHelper::getInstanceByRefId($this->getCourseId());
			}
			else
			{
				$helper = ilCourseBookingHelper::getInstance($crs);
			}
				
			if($this->bookCourseForOthers() && $helper->isBookable($a_user_id))
			{
				return true;
			}
			
			// check org units (book_employees[_rcrsv])
			
			return false;			
		}			
	}
	
	/**
	 * Is user allowed to cancel course for himself?
	 * 
	 * @return bool
	 */
	public function cancelCourseForSelf()
	{
		global $ilAccess;
		
		// ILIAS-Standard ?
		
		// see ilObjCourseAccess::_checkAccess()
		
		return $ilAccess->checkAccessOfUser($this->getUserId(), "leave", "", $this->getCourseId());
	}
	
	/**
	 * Is user allowed to cancel course for others?
	 * 
	 * @return bool
	 */
	public function cancelCourseForOthers()
	{		
		global $ilAccess;
		
		return $ilAccess->checkAccessOfUser($this->getUserId(), "cancel_bookings", "", $this->getCourseId());
	}
	
	/**
	 * Is user allowed to book course for given user?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function cancelCourseForUser($a_user_id)
	{
		$a_user_id = (int)$a_user_id;
		
		if($a_user_id)
		{
			if($this->getUserId() == $a_user_id)
			{
				return $this->cancelCourseForSelf();
			}
			
			if($this->cancelCourseForOthers())
			{
				return true;
			}
			
			// check org units (cancel_employee_bookings[_rcrsv])
			
			return false;			
		}			
	}
}
