<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Accomodations helper 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAccomodations
 */

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilAccomodationsHelper
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
			return self::$instances[$crs_ref_id];
		}	
		
		if(ilObject::_lookupType($a_course_ref_id, true) != "crs" ||
			$tree->isDeleted($a_course_ref_id))
		{
			throw new ilException("ilCourseBookingHelper - needs course ref id");
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
	 * Get course start date
	 * 
	 * @return ilDate
	 */
	public function getCourseStart()
	{
		return $this->utils->getStartDate();
	}
	
	/**
	 * Get course end date
	 * 
	 * @return ilDate
	 */
	public function getCourseEnd()
	{
		return $this->utils->getEndDate();
	}
}