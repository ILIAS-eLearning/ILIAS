<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvErstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDSuccessWPStorno extends WBDSuccess {
	
	protected $agent_id;
	protected $wbd_booking_id;

	const WBD_BOOKING_ID = "WeiterbildungsPunkteBuchungsId";
	const AGENT_ID = "VermittlerId";
	const ROW_ID = "InterneBuchungsId";
	

	public function __construct($response,$row_id) {
		
		$this->row_id = (int)$row_id;
		$this->agent_id = $this->nodeValue($response,self::AGENT_ID);
		$this->wbd_booking_id = $this->nodeValue($response,self::WBD_BOOKING_ID);
	}

	/**
	* @throws LogicException
	* @return user_id
	*/
	public function rowId() {
		return $this->row_id;
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
	* gets the wbd booking id date
	*
	* @throws LogicException
	* 
	*@return ilDate
	*/
	public function wbdBookingId() {
		if($this->wbd_booking_id === null) {
			throw new LogicException("gevWBDSuccessWPMeldung::wbdBookingId:wbd_booking_id is NULL");
		}

		return $this->wbd_booking_id;
	}
}