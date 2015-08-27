<?php
class gevWBDSuccessVvErstanlage extends WBDSuccess {
	protected $internal_agent_id;
	protected $agent_id;
	protected $create_date;
	protected $begin_of_certification_period;

	const INTERNAL_AGENT_ID = "TpInterneVermittlerId";
	const AGENT_ID = "VermittlerId";
	const CREATE_DATE = "AnlageDatum";
	const BEGIN_OF_CERTIFICATION_PERIOD = "BeginnZertifizierungsPeriode";

	public function __construct($response) {
		try {
			$this->internal_agent_id = $this->nodeValue($response,self::INTERNAL_AGENT_ID);
			$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
			$this->create_date = $this->nodeValue($response,self::CREATE_DATE);
			$this->begin_of_certification_period = $this->nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		} catch (LogicException $e) {
			throw new LogicException($e->getMessage());
		} catch (Exception $e) {
			throw new LogicException("gevWBDSuccessVvErstanlage::__construct:unknown error");
		}
	}

	public function internalAgentId() {
		if($this->internal_agent_id === null) {
			throw new LogicException("gevWBDSuccessVvErstanlage::internalAgentId:internal_agent_id is NULL");
		}

		return $this->internal_agent_id;
	}

	public function AgentId() {
		if($this->agent_id === null) {
			throw new LogicException("gevWBDSuccessVvErstanlage::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}

	public function createDate() {
		if($this->create_date === null) {
			throw new LogicException("gevWBDSuccessVvErstanlage::createDate:create_date is NULL");
		}

		return $this->create_date;
	}

	public function beginOfCertificationPeriod() {
		if($this->begin_of_certification_period === null) {
			throw new LogicException("gevWBDSuccessVvErstanlage::beginOfCertificationPeriod:begin_of_certification_period is NULL");
		}

		return $this->begin_of_certification_period;
	}
}