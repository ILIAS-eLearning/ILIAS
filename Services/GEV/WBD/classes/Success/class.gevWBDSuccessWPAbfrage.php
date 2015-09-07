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
	protected $internal_agent_id;
	protected $agent_id;
	protected $wbd_booking_id;
	protected $begin_of_certification_period;
	protected $title;
	protected $credit_points;
	protected $begin_date;
	protected $end_date;
	protected $course_type;
	protected $study_content;
	protected $storno;
	protected $correct_booking;

	const WBD_BOOKING_ID = "WeiterbildungsPunkteBuchungsId";
	const INTERNAL_AGENT_ID = "InterneVermittlerId";
	const AGENT_ID = "VermittlerId";
	const BEGIN_OF_CERTIFICATION_PERIOD = "BeginnErstePeriode";
	const TITLE = "Weiterbildung";
	const CREDIT_POINTS = "WeiterbildungsPunkte";
	const BEGIN_DATE = "SeminarDatumVon";
	const END_DATE = "SeminarDatumBis";
	const COURSE_TYPE = "LernArt";
	const STUDY_CONTENT = "LernInhalt";
	const STORNO = "Storniert";
	const CORRECT_BOOKING = "Korrekturbuchung";

	const DATE_SPLITTER = "T";

	public function __construct($response) {

		$this->dictionary = new gevWBDDictionary();

		$internal_agent_id = $this->nodeValue($response,self::INTERNAL_AGENT_ID);
		if(!is_numeric($internal_agent_id)) {
			throw new LogicException ("gevWBDSuccessWPMeldung::__construct:internal agent is not a number");
		}

		$this->internal_agent_id = (int)$internal_agent_id;
		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
		$this->wbd_booking_id = $this->nodeValue($response,self::WBD_BOOKING_ID);

		$begin_of_certification_period = $this->nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$split = explode($begin_of_certification_period,self::DATE_SPLITTER);
		$this->begin_of_certification_period = new ilDate($split[0],IL_CAL_DATE);

		$begin_date = $this->nodeValue($response,self::BEGIN_DATE);
		$split = explode($begin_date,self::DATE_SPLITTER);
		$this->begin_date = new ilDate($split[0],IL_CAL_DATE);

		$end_date = $this->nodeValue($response,self::END_DATE);
		$split = explode($end_date,self::DATE_SPLITTER);
		$this->end_date = new ilDate($split[0],IL_CAL_DATE);

		$this->title = $this->nodeValue($response,self::TITLE);
		$this->credit_points = $this->nodeValue($response,self::CREDIT_POINTS);
		$this->course_type = $this->dictionary->getInternalName($this->nodeValue($response,self::COURSE_TYPE),gevWBDDictionary::SERACH_IN_COURSE_TYPE);
		$this->study_content = $this->dictionary->getInternalName($this->nodeValue($response,self::STUDY_CONTENT),gevWBDDictionary::SEARCH_IN_STUDY_CONTENT);
		$this->storno = (bool)$this->nodeValue($response,self::STORNO);
		$this->correct_booking = (bool)$this->nodeValue($response,self::CORRECT_BOOKING);
	}



	/**
	* gets the internal agent id
	*
	* @throws LogicException
	* 
	* @return integer
	*/
	public function internalAgentId() {
		if($this->internal_agent_id === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::internalAgentId:internal_agent_id is NULL");
		}

		return $this->internal_agent_id;
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
	* gets the creation date
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function wbdBookingId() {
		if($this->wbd_booking_id === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::wbdBookingId:wbd_booking_id is NULL");
		}

		return $this->wbd_booking_id;
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
	* gets the Title
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function title() {
		if($this->title === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::title:title is NULL");
		}

		return $this->title;
	}

	/**
	* gets the credit_points id
	*
	* @throws LogicException
	* 
	* @return integer
	*/
	public function creditPoints() {
		if($this->credit_points === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::creditPoints:credit_points is NULL");
		}

		return $this->credit_points;
	}

	/**
	* gets the internal agent id
	*
	* @throws LogicException
	* 
	* @return ilDate
	*/
	public function beginDate() {
		if($this->begin_date === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::beginDate:begin_date is NULL");
		}

		return $this->begin_date;
	}

	/**
	* gets the end_date
	*
	* @throws LogicException
	* 
	* @return ilDate
	*/
	public function endDate() {
		if($this->end_date === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::endDate:end_date is NULL");
		}

		return $this->end_date;
	}

	/**
	* gets the course_type
	*
	* @throws LogicException
	* 
	* @return sring
	*/
	public function courseType() {
		if($this->course_type === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::courseType:course_type is NULL");
		}

		return $this->course_type;
	}

	/**
	* gets the study_content
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function studyContent() {
		if($this->study_content === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::studyContent:study_content is NULL");
		}
		
		return $this->study_content;
	}

	/**
	* gets the storno
	*
	* @throws LogicException
	* 
	* @return bool
	*/
	public function storno() {
		if($this->storno === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::storno:storno is NULL");
		}

		return $this->storno;
	}

	/**
	* gets the correct_booking
	*
	* @throws LogicException
	* 
	* @return bool
	*/
	public function correctBooking() {
		if($this->correct_booking === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::correctBooking:correct_booking is NULL");
		}
		
		return $this->correct_booking;
	}
}