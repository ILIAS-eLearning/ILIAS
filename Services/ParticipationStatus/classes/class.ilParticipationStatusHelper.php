<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course participation status helper 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesParticipationsStatus
 */
class ilParticipationStatusHelper
{
	protected $course; // [ilObjCourse]
	
	static protected $instances = array();
	
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
		$crs_ref_id = $a_course->getRefId();
		
		if(!array_key_exists($crs_ref_id, self::$instances))
		{
			self::$instances[$crs_ref_id] = new self($a_course);
		}
		
		return self::$instances[$crs_ref_id];
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
			throw new ilException("ilParticipationStatusHelper - needs course ref id");
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
	// course info
	//
	
	/**
	 * Get course participation status mode
	 * 
	 * @return int
	 */
	public function getParticipationStatusMode()
	{
		// mock
		return ilParticipationStatus::MODE_NON_REVIEWED;
	}
	
	/**
	 * Get course max credit points
	 * 
	 * @return int
	 */
	public function getMaxCreditPoints()
	{
		// mock
		return 1000;
	}
	
	/**
	 * Get course start
	 * 
	 * @return ilDate
	 */
	public function getCourseStart()
	{
		// mock
		$date = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$date->increment(IL_CAL_WEEK, -2);
		return $date;
	}
	
	/**
	 * Get course status/credit points setting start time
	 * 
	 *  @return ilDateTime
	 */
	public function getStartForParticipationStatusSetting()
	{
		$date = new ilDateTime(time(), IL_CAL_UNIX);
		$date->increment(IL_CAL_WEEK, -1);
		return $date;
	}
	
	/**
	 * Is status/credit points setting start time reached?
	 * 
	 * @return bool
	 */
	public function isStartForParticipationStatusSettingReached()
	{
		$dl = $this->getStartForParticipationStatusSetting();
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		return ($dl && ilDateTime::_before($dl, $now));
	}
	
	/**
	 * Has course required attendance list?
	 * 
	 * @return boolean
	 */
	public function getCourseNeedsAttendanceList()
	{
		return true;
	}	
}