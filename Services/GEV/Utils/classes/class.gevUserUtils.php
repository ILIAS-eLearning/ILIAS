<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");

class gevUserUtils {
	static protected $instances = array();

	protected function __construct($a_user_id) {
		$this->user_id = $a_user_id;
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
		return 
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
	}
}

?>