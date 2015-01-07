<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Material list helper 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMaterialList
 */

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilMaterialListHelper
{
	protected $course_id; // [int]
	
	static protected $instances = array();
	
	/**
	 * Constructor
	 * 
	 * @param int $a_crs_obj_id
	 * @return self
	 */	
	protected function __construct($a_crs_obj_id)
	{
		$this->setCourseId($a_crs_obj_id);				
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_crs_obj_id
	 * @return self
	 */
	public static function getInstance($a_crs_obj_id)
	{			
		if(!array_key_exists($a_crs_obj_id, self::$instances))
		{
			self::$instances[$a_crs_obj_id] = new self($a_crs_obj_id);
		}
		
		return self::$instances[$a_crs_obj_id];
	}
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param int $a_crs_obj_id
	 */
	protected function setCourseId($a_crs_obj_id)
	{
		$this->course_id = $a_crs_obj_id;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourseId()
	{
		return $this->course_id;
	}
	
	
	// 
	// course info
	// 
	
	/**
	 * Get custom id
	 *
	 * @return string
	 */
	public function getCustomId()
	{				
		return gevCourseUtils::getInstance($this->course_id)->getCustomId();
	}
	
	/**
	 * Get trainer
	 *
	 * @return string
	 */
	public function getTrainer()
	{
		$cu = gevCourseUtils::getInstance($this->course_id);
		$name = $cu->getMainTrainerName();
		return $name?$name." (".$cu->getMainTrainerEMail().")":"";
	}
	
	/**
	 * Get venue info
	 *
	 * @return array
	 */
	public function getVenueInfo()
	{
		$cu = gevCourseUtils::getInstance($this->course_id);
		return array(
			  $cu->getVenueTitle()
			, $cu->getVenueStreet()." ".$cu->getVenueHouseNumber()
			, $cu->getVenueZipcode()." ".$cu->getVenueCity()
			, $cu->getVenueEmail()
			, $cu->getVenuePhone()
			);
	}
	
	/**
	 * Get venue info
	 *
	 * @return string
	 */
	public function getContact()
	{				
		return "Ad-Schulung.de@generali.com";
	}
	
	/**
	 * Get venue info
	 *
	 * @return array
	 */
	public function getDateInfo()
	{
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$cu = gevCourseUtils::getInstance($this->course_id);
		$start_date = $cu->getStartDate();
		$end_date = $cu->getEndDate();
		if ($start_date === null) {
			return array();
		}
		$end_date_str = $end_date->get(IL_CAL_DATE);
		$ret = array();
		$schedule = explode(";", $cu->getFormattedSchedule(";"));
		$sched_count = 0;
		while ($start_date->get(IL_CAL_DATE) <= $end_date_str) {
			$ret[] = ilDatePresentation::formatDate($start_date)
					." ".$schedule[$sched_count];
			$sched_count++;
			$start_date->increment(ilDateTime::DAY, 1);
		}
		return $ret;
	}
	
	/**
	 * Get number of participants
	 *
	 * @return int
	 */
	public function getAmountOfParticipants()
	{				
		return count(gevCourseUtils::getInstance($this->course_id)->getParticipants());
	}
}