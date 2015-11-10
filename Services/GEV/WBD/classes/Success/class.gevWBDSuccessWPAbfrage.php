<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvErstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Service/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
class gevWBDSuccessWPAbfrage extends WBDSuccessWPAbfrage {
	use gevWBDSuccess;

	protected $user_id;
	protected $import_course_data;

	public function __construct($response, $user_id) {
		parent::__construct($response);

		$this->user_id = $user_id;
		$this->dictionary = new gevWBDDictionary();
		$this->import_course_data = array();

		foreach ($this->toImportCourseValues as $key => $value) {
			if(boolval($value[self::CORRECT_BOOKING]) || boolval($value[self::STORNO])) {
				continue;
			}

			$wbd_booking_id = $value[self::WBD_BOOKING_ID];

			$begin_date = $value[self::BEGIN_DATE];
			$split = explode(self::DATE_SPLITTER,$begin_date);
			$begin_date = new ilDate($split[0],IL_CAL_DATE);

			$end_date = $value[self::END_DATE];
			$split = explode(self::DATE_SPLITTER,$end_date);
			$end_date = new ilDate($split[0],IL_CAL_DATE);

			$title = $value[self::TITLE];
			$credit_points = $value[self::CREDIT_POINTS];
			$course_type = $this->dictionary->getInternalName($value[self::COURSE_TYPE],gevWBDDictionary::SERACH_IN_COURSE_TYPE);
			$study_content = $this->dictionary->getInternalName($value[self::STUDY_CONTENT],gevWBDDictionary::SEARCH_IN_STUDY_CONTENT);

			$this->import_course_data[] = new gevImportCourseData($wbd_booking_id, $begin_date, $end_date, $title, $credit_points, $course_type, $study_content);
		}
	}

	/**
	* gets the user id
	*
	* @throws LogicException
	* 
	* @return integer
	*/
	public function userId() {
		if($this->user_id === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::internalAgentId:userId is NULL");
		}

		return $this->user_id;
	}

	/**
	* gets the import course data
	* 
	* @return array
	*/
	public function importCourseData() {
		return $this->import_course_data;
	}
}