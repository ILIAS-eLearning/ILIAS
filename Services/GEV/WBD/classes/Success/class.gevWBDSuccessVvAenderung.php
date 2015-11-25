<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBD Success for Service VvAenderung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDSuccessVvAenderung extends WBDSuccessVvAenderung {
	protected $row_id;

	public function __construct($response, $row_id) {
		if(!$row_id) {
			throw new LogicException("gevWBDSuccessVvAenderung: a row_id must be provided");
		}
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