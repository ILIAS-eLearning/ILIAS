<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for generali users.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/CourseBooking/classes/class.ilUserCourseBookings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevUserUtils {
	static protected $instances = array();

	protected function __construct($a_user_id) {
		$this->user_id = $a_user_id;
		$this->courseBookings = ilUserCourseBookings::getInstance($a_user_id);
	}
	
	static public function getInstance($a_user_id) {
		if (array_key_exists($a_user_id, self::$instances)) {
			return self::$instances[$a_user_id];
		}
		
		self::$instances[$a_user_id] = new gevUserUtils($a_user_id);
		return self::$instances[$a_user_id];
	}
	
	public function getNextCourse() {
		return 0;	// TODO: implement that properly
	}
	
	public function getLastCourse() {
		return 0;	// TODO: implement that properly
	}
	
	public function getEduBioLink() {
		return "http://www.google.de"; //TODO: implement this properly
	}
	
	public function getBookedAndWaitingCourseInformation() {
		$crs_amd = 
			array( gevSettings::CRS_AMD_START_DATE			=> "start_date"
				 , gevSettings::CRS_AMD_END_DATE 			=> "end_date"
				 , gevSettings::CRS_AMD_CANCEL_DEADLINE			=> "cancel_date"
				 //, gevSettings::CRS_AMD_ => "title"
				 //, gevSettings::CRS_AMD_START_DATE => "status"
				 , gevSettings::CRS_AMD_TYPE 				=> "type"
				 , gevSettings::CRS_AMD_VENUE 				=> "location"
				 , gevSettings::CRS_AMD_CREDIT_POINTS 		=> "credit_points"
				 , gevSettings::CRS_AMD_FEE					=> "fee"
				 , gevSettings::CRS_AMD_TARGET_GROUP_DESC	=> "target_group"
				 , gevSettings::CRS_AMD_GOALS 				=> "goals"
				 , gevSettings::CRS_AMD_CONTENTS 			=> "content"
			);
		
		
		$booked = $this->courseBookings->getBookedCourses();
		$booked_amd = gevAMDUtils::getInstance()->getTable($booked, $crs_amd);
		foreach ($booked_amd as $key => $value) {
			$booked_amd[$key]["status"] = ilCourseBooking::STATUS_BOOKED;
			$booked_amd[$key]["cancel_date"] = gevCourseUtils::mkCancelDate( $booked_amd[$key]["start_date"]
																		   , $booked_amd[$key]["cancel_date"]
																		   );
		}
		$waiting = $this->courseBookings->getWaitingCourses();
		$waiting_amd = gevAMDUtils::getInstance()->getTable($waiting, $crs_amd);
		foreach ($waiting_amd as $key => $value) {
			$waiting_amd[$key]["status"] = ilCourseBooking::STATUS_WAITING;
			$waiting_amd[$key]["cancel_date"] = gevCourseUtils::mkCancelDate( $waiting_amd[$key]["start_date"]
																			, $waiting_amd[$key]["cancel_date"]
																			);
		}
		
		return array_merge($booked_amd, $waiting_amd);
	}
	
	public function hasUserSelectorOnSearchGUI() {
		return true; // TODO: Implement that properly.
	}
	
	public function getEmployeesForCourseSearch() {
		// TODO: Implement that properly
		global $ilDB;
		$res = $ilDB->query("SELECT usr_id, firstname, lastname FROM usr_data");
		$ret = array();
		while($val = $ilDB->fetchAssoc($res)) {
			$ret[] = $val;
		}
		return $ret;
	}
}

?>