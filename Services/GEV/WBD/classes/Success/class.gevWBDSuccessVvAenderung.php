<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvAenderung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDSuccessVvAenderung extends WBDSuccess {
	protected $agent_id;

	const AGENT_ID = "VermittlerId";
	
	public function __construct($response) {
		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
	}
	
	/**
	* gets the WBD Agent id
	*
	* @throws LogicException
	* 
	*@return string
	*/
	public function AgentId() {
		if($this->agent_id === null) {
			throw new LogicalException("gevWBDSuccessVvErstanlage::AgentId:agent_id is NULL");
		}

		return $this->agent_id;
	}
}