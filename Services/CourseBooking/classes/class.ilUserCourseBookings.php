<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/CourseBooking/classes/class.ilCourseBooking.php";

/**
 * User bookings
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilUserCourseBookings
{
	protected $user_id; // [int]
	
	static protected $instances = array();
	
	/**
	 * Constructor
	 * 
	 * @param int $a_user_id
	 * @return self
	 */	
	protected function __construct($a_user_id)
	{
		$this->setUserId($a_user_id);				
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_user_id
	 * @return self
	 */
	public static function getInstance($a_user_id)
	{		
		if(!array_key_exists($a_user_id, self::$instances))
		{
			self::$instances[$a_user_id] = new self($a_user_id);
		}
		
		return self::$instances[$a_user_id];
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set user
	 * 
	 * @param int $a_user_id
	 */
	protected function setUserId($a_user_id)
	{
		$this->user_id = (int)$a_user_id;
	}
	
	/**
	 * Get user
	 * 
	 * @return int
	 */	
	protected function getUserId()
	{
		return $this->user_id;
	}
	
	
	//
	// user info
	//
	
	/**
	 * Get user courses by status
	 * 
	 * @param int|array $a_status
	 * @return array
	 */
	protected function getCoursesByStatus($a_status)
	{
		return ilCourseBooking::getCoursesByStatus($this->getUserId(), $a_status);
	}
	
	/**
	 * Get ids of booked courses
	 * 
	 * @return array
	 */
	public function getBookedCourses()
	{
		return $this->getCoursesByStatus(ilCourseBooking::STATUS_BOOKED);
	}
	
	/**
	 * Get ids of courses with waiting status
	 * 
	 * @return array
	 */
	public function getWaitingCourses()
	{
		return $this->getCoursesByStatus(ilCourseBooking::STATUS_WAITING);
	}
	
	/**
	 * Get ids of courses with booked or waiting status
	 * 
	 * @return array
	 */
	public function getBookedAndWaitingCourses()
	{
		return $this->getCoursesByStatus(array(ilCourseBooking::STATUS_BOOKED, 
			ilCourseBooking::STATUS_WAITING));
	}
	
	/**
	 * Get ids of cancelled with costs courses
	 * 
	 * @return array
	 */
	public function getCancelledWithCostsCourses()
	{
		return $this->getCoursesByStatus(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS);
	}
	
	/**
	 * Get ids of cancelled without costs courses
	 * 
	 * @return array
	 */
	public function getCancelledWithoutCostsCourses()
	{
		return $this->getCoursesByStatus(ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS);
	}
	
	/**
	 * Get ids of courses with any cancelled status
	 * 
	 * @return array
	 */
	public function getCancelledCourses()
	{
		return $this->getCoursesByStatus(array(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS, 
			ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS));
	}
	
	/**
	 * Get ids of courses with booked or waiting status in certain period
	 * 
	 * @param ilDate $a_start
	 * @param ilDate $a_end
	 * @return array
	 */
	public function getCoursesDuring(ilDate $a_start, ilDate $a_end)
	{
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/Calendar/classes/class.ilDateTime.php");
	
		global $ilDB;
	
		$all = $this->getBookedAndWaitingCourses();
		
		$amd = array( gevSettings::CRS_AMD_START_DATE => "start_date"
					, gevSettings::CRS_AMD_END_DATE => "end_date"
					);
		
		$dates = gevAMDUtils::getInstance()
							->getTable($all, $amd, array()
									  , array(" JOIN crs_book crsbk ON od.obj_id = crsbk.crs_id")
									  , " AND amd0.value <= ".$ilDB->quote($a_end->get(IL_CAL_DATE), "text")
									  . " AND amd1.value >= ".$ilDB->quote($a_start->get(IL_CAL_DATE), "text")
									  . " AND crsbk.user_id = ".$ilDB->quote($this->getUserId())
									  . " AND ".$ilDB->in("crsbk.status", array( ilCourseBooking::STATUS_BOOKED
									  										   , ilCourseBooking::STATUS_WAITING)
									  					 , false, "integer"
									  					 )
									  );
		
		return $dates;
	}	
}