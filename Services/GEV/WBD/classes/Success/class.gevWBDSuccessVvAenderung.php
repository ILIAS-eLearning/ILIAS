<?php
class gevWBDSuccessVvAenderung extends WBDSuccess {
	protected $agent_id;

	const AGENT_ID = "VermittlerId";
	
	public function __construct($response) {
		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
	}

	public function AgentId() {
		if($this->agent_id === null) {
			throw new LogicalException("gevWBDSuccessVvErstanlage::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}
}