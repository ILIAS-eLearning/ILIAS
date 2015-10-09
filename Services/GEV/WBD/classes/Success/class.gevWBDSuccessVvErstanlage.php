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
class gevWBDSuccessVvErstanlage extends WBDSuccess {
	protected $internal_agent_id;
	protected $agent_id;
	protected $create_date;
	protected $begin_of_certification_period;
	protected $row_id;

	const INTERNAL_AGENT_ID = "TpInterneVermittlerId";
	const AGENT_ID = "VermittlerId";
	const CREATE_DATE = "AnlageDatum";
	const BEGIN_OF_CERTIFICATION_PERIOD = "BeginnZertifizierungsPeriode";
	const DATE_SPLITTER = "T";

	public function __construct($response, $row_id) {
		
		$internal_agent_id = $this->nodeValue($response,self::INTERNAL_AGENT_ID);
		if(!is_numeric($internal_agent_id)) {
			throw new LogicException ("gevWBDSuccessVvErstanlage::__construct:internal agent is not a number");
		}
		if(!is_numeric($row_id)) {
			throw new LogicException ("gevWBDSuccessVvErstanlage::__construct:row_id is not a number");
		}
		$this->row_id = $row_id;
		$this->internal_agent_id = (int)$internal_agent_id;
		
		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);

		$create_date = $this->nodeValue($response,self::CREATE_DATE);
		$split = explode(self::DATE_SPLITTER,$create_date);
		$this->create_date = new ilDate($split[0],IL_CAL_DATE);

		$begin_of_certification_period = $this->nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$split = explode(self::DATE_SPLITTER,$begin_of_certification_period);
		$this->begin_of_certification_period = new ilDate($split[0],IL_CAL_DATE);
	}

	/**
	* @throws LogicException
	* @return user_id
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
			throw new LogicException("gevWBDSuccessVvErstanlage::internalAgentId:internal_agent_id is NULL");
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
			throw new LogicException("gevWBDSuccessVvErstanlage::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}

	/**
	* gets the creation date
	*
	* @throws LogicException
	* 
	*@return ilDate
	*/
	public function createDate() {
		if($this->create_date === null) {
			throw new LogicException("gevWBDSuccessVvErstanlage::createDate:create_date is NULL");
		}

		return $this->create_date;
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
			throw new LogicException("gevWBDSuccessVvErstanlage::beginOfCertificationPeriod:begin_of_certification_period is NULL");
		}

		return $this->begin_of_certification_period;
	}
}