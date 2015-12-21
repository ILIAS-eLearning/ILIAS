<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VermittlerVerwaltung Transferfähig machen
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
class gevWBDSuccessVermitVerwaltungTransferfaehig extends WBDSuccessVermitVerwaltungTransferfaehig {
	use gevWBDSuccess;
	
	protected $usr_id;
	protected $row_id;

	public function __construct($usr_id, $row_id) {
		parent::__construct();

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

	/**
	* gets the row id
	*
	* @throws LogicException
	* 
	*@return integer
	*/
	public function rowId() {
		return $this->row_id;
	}
}