<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course booking helper 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilCourseBookingHelper
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
	 * Get course booking deadline
	 * 
	 * @return ilDate
	 */
	public function getBookingDeadline()
	{
		return gevCourseUtils::getInstance($this->course->getId())->getBookingDeadlineDate();
	}
	
	/**
	 * Is course booking deadline reached?
	 * 
	 * @return bool
	 */
	public function isBookingDeadlineReached()
	{
		$dl = $this->getBookingDeadline();
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		return ($dl && ilDateTime::_before($dl, $now));
	}
	
	/**
	 * Get course cancellation deadline
	 * 
	 * @return ilDate
	 */
	public function getCancellationDeadline()
	{
		return gevCourseUtils::getInstance($this->course->getId())->getCancelDeadlineDate();
	}
	
	/**
	 * Is course cancel deadline reached?
	 * 
	 * @return bool
	 */
	public function isCancellationDeadlineReached()
	{
		$dl = $this->getCancellationDeadline();
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		return ($dl && ilDateTime::_before($dl, $now));
	}
	
	/**
	 * Get course ultimate booking deadline
	 * 
	 * @return ilDate
	 */
	public function getUltimateBookingDeadline()
	{
		$end_date = gevCourseUtils::getInstance($this->course->getId())->getEndDate();
		$end_date->increment(IL_CAL_DAY, 1);
		return $end_date;
	}
	
	/**
	 * Is course ultimate booking deadline reached?
	 * 
	 * @return bool
	 */
	public function isUltimateBookingDeadlineReached()
	{
		$udl = $this->getUltimateBookingDeadline();
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		return ($udl && ilDateTime::_before($udl, $now));
	}
	
	/**
	 * Get course start date
	 * 
	 * @return ilDate
	 */
	public function getCourseStart()
	{
		return gevCourseUtils::getInstance($this->course->getId())->getStartDate();
	}
	
	/**
	 * Get course end date
	 * 
	 * @return ilDate
	 */
	public function getCourseEnd()
	{
		return gevCourseUtils::getInstance($this->course->getId())->getEndDate();
	}
	
	/**
	 * Is course bookable?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function isBookable($a_user_id = null)
	{								
		// see ilCourseRegistrationGUI::validate 						
		require_once "Modules/Course/classes/class.ilObjCourseGrouping.php";
		if(!ilObjCourseGrouping::_checkGroupingDependencies($this->getCourse(), $a_user_id))
		{
			return false;
		}
		
		// :TODO: course agreement, (mandatory) custom course data ?!
		
		// see ilCourseBookingPermissions::bookCourseForSelf()
		
		// gev-patch start
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$tmplt_ref_id = gevCourseUtils::getInstanceByObj($this->getCourse())->getTemplateRefId();
		if (!gevUserUtils::getInstance($a_user_id)->canBookCourseDerivedFromTemplate($tmplt_ref_id)) {
			return false;
		}
		// gev-patch end
		
		return true;				
	}	
	
	
	//
	// ORG UNIT
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
}