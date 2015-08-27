<?php
class gevWBDSuccessVvAenderung extends WBDSuccess {
	protected $agent_id;

	const AGENT_ID = "VermittlerId";
	
	public function __construct($response) {
		try {
			$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
		} catch (LogicException $e) {
			throw new LogicException($e->getMessage());
		} catch (Exception $e) {
			throw new LogicException("gevWBDSuccessVvErstanlage::__construct:unknown error");
		}
	}

	public function AgentId() {
		if($this->agent_id === null) {
			throw new LogicalException("gevWBDSuccessVvErstanlage::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}
}