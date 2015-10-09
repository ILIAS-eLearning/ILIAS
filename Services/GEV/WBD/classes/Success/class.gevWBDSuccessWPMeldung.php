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
class gevWBDSuccessWPMeldung extends WBDSuccess {
	protected $internal_agent_id;
	protected $agent_id;
	protected $wbd_booking_id;
	protected $begin_of_certification_period;
	protected $row_id;
	protected $user_id;

	const WBD_BOOKING_ID = "WeiterbildungsPunkteBuchungsId";
	const INTERNAL_AGENT_ID = "InterneVermittlerId";
	const AGENT_ID = "VermittlerId";
	const ROW_ID = "InterneBuchungsId";
	const BEGIN_OF_CERTIFICATION_PERIOD = "BeginnErstePeriode";
	const DATE_SPLITTER = "T";

	public function __construct($response, $begin_of_certification, $user_id) {
		$internal_agent_id = $this->nodeValue($response,self::INTERNAL_AGENT_ID);
		if(!is_numeric($internal_agent_id)) {
			throw new LogicException ("gevWBDSuccessWPMeldung::__construct:internal agent is not a number");
		}
		
		$row_id = $this->nodeValue($response,self::ROW_ID);
		if(!is_numeric($row_id)) {
			throw new LogicException ("gevWBDSuccessWPMeldung::__construct:row_id is not a number");
		}

		$this->internal_agent_id = (int)$internal_agent_id;
		$this->row_id = (int)$row_id;
		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
		$this->wbd_booking_id = $this->nodeValue($response,self::WBD_BOOKING_ID);

		$begin_of_certification_period = $this->nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$split = explode(self::DATE_SPLITTER,$begin_of_certification_period);
		$this->begin_of_certification_period = new ilDate($split[0],IL_CAL_DATE);

		$this->old_begin_of_certification = new ilDate($begin_of_certification,IL_CAL_DATE);
		$this->user_id = $user_id;
	}

	/**
	* @throws LogicException
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets the internal agent id
	*
	* @throws LogicException
	* 
	*@return integer
	*/
	public function internalAgentId() {
		if($this->internal_agent_id === null) {
			throw new LogicException("gevWBDSuccessWPMeldung::internalAgentId:internal_agent_id is NULL");
		}

		return $this->internal_agent_id;
	}

	/**
	* gets the WBD Agent id
	*
	* @throws LogicException
	* 
	*@return string
	*/
	public function agentId() {
		if($this->agent_id === null) {
			throw new LogicException("gevWBDSuccessWPMeldung::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}

	/**
	* gets the creation date
	*
	* @throws LogicException
	* 
	*@return string
	*/
	public function wbdBookingId() {
		if($this->wbd_booking_id === null) {
			throw new LogicException("gevWBDSuccessWPMeldung::wbdBookingId:wbd_booking_id is NULL");
		}

		return $this->wbd_booking_id;
	}

	/**
	* gets the begin of the certification period
	*
	* @throws LogicException
	* 
	*@return ilDate
	*/
	public function beginOfCertificationPeriod() {
		if($this->begin_of_certification_period === null) {
			throw new LogicException("gevWBDSuccessWPMeldung::beginOfCertificationPeriod:begin_of_certification_period is NULL");
		}
		
		return $this->begin_of_certification_period;
	}

	/**
	* gets the user_id
	* @return integer
	*/
	public function usrId() {
		return $this->user_id;
	}

	/**
	* should the begin_of_certification be updated
	*
	* @return boolean
	*/
	public function doUpdateBeginOfCertification(){
		if($this->begin_of_certification_period->get(IL_CAL_UNIX) != $this->old_begin_of_certification->get(IL_CAL_UNIX)) {
			return true;
		}

		return false;
	}
}