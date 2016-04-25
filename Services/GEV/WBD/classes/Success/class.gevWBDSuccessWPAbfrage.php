<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvErstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");

class gevWBDSuccessWPAbfrage extends WBDSuccessWPAbfrage {
	use gevWBDSuccess;

	protected $user_id;

	public function __construct($response, $user_id) {
		parent::__construct($response);

		$this->user_id = $user_id;
		$this->import_course_data = array();

		$begin_of_certification_period = self::nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$this->begin_of_certification_period = $this->createDate($begin_of_certification_period);

		$this->toImportCourseNodes = array(ImportCourseData::WBD_BOOKING_ID
									,ImportCourseData::TITLE
									,ImportCourseData::CREDIT_POINTS
									,ImportCourseData::BEGIN_DATE
									,ImportCourseData::END_DATE
									,ImportCourseData::COURSE_TYPE
									,ImportCourseData::STUDY_CONTENT 
									,ImportCourseData::STORNO
									,ImportCourseData::CORRECT_BOOKING
									);

		foreach ($this->toImportCourseValues as $key => $value) {
			if(boolval($value[ImportCourseData::CORRECT_BOOKING]) || boolval($value[ImportCourseData::STORNO])) {
				continue;
			}
			
			$value[ImportCourseData::BEGIN_DATE] = $this->createDate($value[ImportCourseData::BEGIN_DATE]);
			$value[ImportCourseData::END_DATE] = $this->createDate($value[ImportCourseData::END_DATE]);
			$value[ImportCourseData::COURSE_TYPE] = $this->getDictionary()->getInternalName($value[ImportCourseData::COURSE_TYPE]
																	,gevWBDDictionary::SERACH_IN_COURSE_TYPE);
			$value[ImportCourseData::STUDY_CONTENT] = $this->getDictionary()->getInternalName($value[ImportCourseData::STUDY_CONTENT]
																	,gevWBDDictionary::SEARCH_IN_STUDY_CONTENT);

			$this->import_course_data[] = new gevImportCourseData($value);
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