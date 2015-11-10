<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VermittlerVerwaltung Transferfähig machen
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDSuccessVermitVerwaltungAufnahme extends WBDSuccessVermitVerwaltungAufnahme {
	protected $usr_id;
	protected $row_id;

	public function __construct($usr_id, $row_id) {
		if(!$usr_id) {
			throw new LogicException("gevWBDSuccessVermitVerwaltungAufnahme: a usr_id must be provided");
		}
		
		if(!$row_id) {
			throw new LogicException("gevWBDSuccessVermitVerwaltungAufnahme: a row_id must be provided");
		}

		$this->row_id = $row_id;
		$this->usr_id = $usr_id;
	}
	
	/**
	* gets the WBD Agent id
	*
	* @throws LogicException
	* 
	*@return string
	*/
	public function usrId() {
		return $this->usr_id;
	}

	public function rowId() {
		return $this->row_id;
	}
}