<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvErstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
class gevWBDSuccessWPAbfrage extends WBDSuccess {
	protected $user_id;
	protected $agent_id;
	protected $begin_of_certification_period;
	protected $import_course_data;


	const AGENT_ID = "VermittlerId";
	const BEGIN_OF_CERTIFICATION_PERIOD = "BeginnErstePeriode";
	const WBD_BOOKING_LIST = "WeiterbildungsPunkteBuchungListe";

	//TAG NAMES FOR IMPORT CRS DATA
	const WBD_BOOKING_ID = "WeiterbildungsPunkteBuchungsId";
	const TITLE = "Weiterbildung";
	const CREDIT_POINTS = "WeiterbildungsPunkte";
	const BEGIN_DATE = "SeminarDatumVon";
	const END_DATE = "SeminarDatumBis";
	const COURSE_TYPE = "LernArt";
	const STUDY_CONTENT = "LernInhalt";
	const STORNO = "Storniert";
	const CORRECT_BOOKING = "Korrekturbuchung";

	const DATE_SPLITTER = "T";

	public function __construct($response, $user_id) {

		$this->dictionary = new gevWBDDictionary();
		$this->import_course_data = array();

		$this->user_id = $user_id;

		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
		$begin_of_certification_period = $this->nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$split = explode($begin_of_certification_period,self::DATE_SPLITTER);
		$this->begin_of_certification_period = new ilDate($split[0],IL_CAL_DATE);

		$toImportCourseNodes = array(self::WBD_BOOKING_ID
									,self::TITLE
									,self::CREDIT_POINTS
									,self::BEGIN_DATE
									,self::END_DATE
									,self::COURSE_TYPE
									,self::STUDY_CONTENT 
									,self::STORNO
									,self::CORRECT_BOOKING
									);

		$toImportCourseValues = $this->nodeValuesByPath($response,self::WBD_BOOKING_LIST,$toImportCourseNodes);

		foreach ($toImportCourseValues as $key => $value) {
			if(boolval($value[self::CORRECT_BOOKING]) || boolval($value[self::STORNO])) {
				continue;
			}

			$wbd_booking_id = $value[self::WBD_BOOKING_ID];

			$begin_date = $value[self::BEGIN_DATE];
			$split = explode($begin_date,self::DATE_SPLITTER);
			$begin_date = new ilDate($split[0],IL_CAL_DATE);

			$end_date = $value[self::END_DATE];
			$split = explode($end_date,self::DATE_SPLITTER);
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
	* gets the WBD Agent id
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function agentId() {
		if($this->agent_id === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}

	/**
	* gets the begin of the certification period
	*
	* @throws LogicException
	* 
	* @return ilDate
	*/
	public function beginOfCertificationPeriod() {
		if($this->begin_of_certification_period === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::beginOfCertificationPeriod:begin_of_certification_period is NULL");
		}
		
		return $this->begin_of_certification_period;
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