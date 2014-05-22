<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
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
		$this->amd = gevAMDUtils::getInstance();
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
		$booked_amd = $this->amd->getTable($booked, $crs_amd);
		foreach ($booked_amd as $key => $value) {
			$booked_amd[$key]["status"] = ilCourseBooking::STATUS_BOOKED;
			$booked_amd[$key]["cancel_date"] = gevCourseUtils::mkCancelDate( $booked_amd[$key]["start_date"]
																		   , $booked_amd[$key]["cancel_date"]
																		   );
		}
		$waiting = $this->courseBookings->getWaitingCourses();
		$waiting_amd = $this->amd->getTable($waiting, $crs_amd);
		foreach ($waiting_amd as $key => $value) {
			$waiting_amd[$key]["status"] = ilCourseBooking::STATUS_WAITING;
			$waiting_amd[$key]["cancel_date"] = gevCourseUtils::mkCancelDate( $waiting_amd[$key]["start_date"]
																			, $waiting_amd[$key]["cancel_date"]
																			);
		}
		
		return array_merge($booked_amd, $waiting_amd);
		
/*		return 
		array(array( "start_date" => new ilDateTime("2014-05-04 13:37:00", IL_CAL_DATETIME)
				   , "end_date" => new ilDateTime("2014-15-05 13:38:00", IL_CAL_DATETIME)
				   , "cancel_date" => new ilDateTime("2014-15-03 13:36:00", IL_CAL_DATETIME)
				   , "obj_id" => 10
				   , "title" => "Mockkurs"
				   , "status" => ilCourseBooking::STATUS_BOOKED
				   , "type" => "Webinar"
				   , "location" => "Kölle"
				   , "credit_points" => 666
				   , "fee" => 19.95
				   , "target_group" => "Jeder, seine Oma und ihr Hund."
				   , "goals" => "Einen Kurs mocken"
				   , "contents" => "Inhalt, Inhalt, Inhalt."
				   ));
*/
	}
}

?>