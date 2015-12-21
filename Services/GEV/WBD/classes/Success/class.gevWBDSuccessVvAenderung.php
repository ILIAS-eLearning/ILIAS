<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvAenderung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
class gevWBDSuccessVvAenderung extends WBDSuccessVvAenderung {
	use gevWBDSuccess;

	protected $row_id;

	public function __construct($response, $row_id) {
		parent::__construct($response);

		$this->row_id = $row_id;
	}

	/**
	* gets the row id
	*
	* @throws LogicException
	* 
	*@return integer
	*/
	public function rowId() {
		if($this->row_id === null) {
			throw new LogicalException("gevWBDSuccessVvErstanlage::rowId:row_id is NULL");
		}
		return $this->row_id;
	}
}