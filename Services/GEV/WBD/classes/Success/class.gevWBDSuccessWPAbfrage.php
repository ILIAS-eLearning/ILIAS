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
require_once("Services/GEV/WBD/classes/Success/class.gevImportCourseData.php");

class gevWBDSuccessWPAbfrage extends WBDSuccessWPAbfrage {
	use gevWBDSuccess;

	protected $user_id;

	public function __construct($response, $user_id) {
		$this->user_id = $user_id;
		$this->import_course_data = array();

		$this->agent_id = self::firstNodeValue($response,self::AGENT_ID);

		$begin_of_certification_period = self::nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$this->begin_of_certification_period = $this->createDate($begin_of_certification_period);

		$this->certification_period = self::nodeValue($response,self::CERTIFICATION_PERIOD);
		$this->okz = self::nodeValue($response,self::OKZ);
		$this->period_total_points = self::nodeValue($response,self::PERIOD_TOTAL_POINTS);
		$this->interna_agent_id = self::nodeValue($response,self::INTERNAL_AGENT_ID);

		$this->toImportCourseNodes = array(gevImportCourseData::WBD_BOOKING_ID
									,gevImportCourseData::TITLE
									,gevImportCourseData::CREDIT_POINTS
									,gevImportCourseData::BEGIN_DATE
									,gevImportCourseData::END_DATE
									,gevImportCourseData::COURSE_TYPE
									,gevImportCourseData::STUDY_CONTENT
									,gevImportCourseData::STORNO
									,gevImportCourseData::CORRECT_BOOKING
									);

		$this->toImportCourseValues = self::nodeValuesByPath($response,self::WBD_BOOKING_LIST,$this->toImportCourseNodes);

		foreach ($this->toImportCourseValues as $key => $value) {
			if($value[gevImportCourseData::CORRECT_BOOKING] == 'true' || $value[gevImportCourseData::STORNO] == 'true') {
				continue;
			}
			$value[gevImportCourseData::BEGIN_DATE] = $this->createDate($value[gevImportCourseData::BEGIN_DATE]);
			$value[gevImportCourseData::END_DATE] = $this->createDate($value[gevImportCourseData::END_DATE]);
			$value[gevImportCourseData::COURSE_TYPE] = $this->getDictionary()->getInternalName($value[gevImportCourseData::COURSE_TYPE]
																	,gevWBDDictionary::SERACH_IN_COURSE_TYPE);
			$value[gevImportCourseData::STUDY_CONTENT] = $this->getDictionary()->getInternalName($value[gevImportCourseData::STUDY_CONTENT]
																	,gevWBDDictionary::SEARCH_IN_STUDY_CONTENT);
			$value[gevImportCourseData::BOOKING_DATE] = $this->createDate($value[gevImportCourseData::BOOKING_DATE]);

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