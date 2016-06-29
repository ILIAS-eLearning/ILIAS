<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

// gev-patch start
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
// gev-patch end

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
		global $ilLog;
		$this->log = &$ilLog;
		
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
		// gev-patch start
		$this->utils = gevCourseUtils::getInstanceByObj($a_course);
		// gev-patch end
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
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
		$type = $this->utils->getType();
		switch ($type) {
			case "Präsenztraining":
				return ilParticipationStatus::MODE_NON_REVIEWED;
			case "Selbstlernkurs":
				return ilParticipationStatus::MODE_CONTINUOUS;
			case "Webinar":
				return ilParticipationStatus::MODE_NON_REVIEWED;
			case "Virtuelles Training":
				return ilParticipationStatus::MODE_NON_REVIEWED;
			default:
				$this->log->write( "ilParticipationStatusHelper::getParticipationStatusMode: "
								 . "Unknown type '".$type."'");
				return ilParticipationStatus::MODE_REVIEWED;
		}
	}
	
	/**
	 * Get course max credit points
	 * 
	 * @return int
	 */
	public function getMaxCreditPoints()
	{
		return $this->utils->getCreditPoints();
	}
	
	/**
	 * Get course start
	 * 
	 * @return ilDate
	 */
	public function getCourseStart()
	{
		return $this->utils->getStartDate();
	}
	
	/**
	 * Get course status/credit points setting start time
	 * 
	 *  @return ilDateTime
	 */
	public function getStartForParticipationStatusSetting()
	{
		return $this->utils->getStartDate();
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
		return ($this->utils->isWebinar() || $this->utils->isDecentralTraining()) && ($this->utils->getCreditPoints() > 0);
	}
	
	// gev-patch start
	/**
	 * Is the trainer obliged to confirm that an invitation mail was send
	 * before he is allowed to finalize the participation status?
	 *
	 * @return boolean
	 */
	public function getCourseNeedsInvitationMailConfirmation() {
		return false;
		return $this->utils->isDecentralTraining();
	}
	// gev-patch end
}