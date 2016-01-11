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
require_once("Services/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");

class gevWBDSuccessWPStorno extends WBDSuccessWPStorno {
	use gevWBDSuccess;
	
	public function __construct($response,$row_id) {
		parent::__construct($response);
		$this->row_id = (int)$row_id;

		$begin_of_certification_period = $this->nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$this->begin_of_certification_period = $this->createDate($begin_of_certification_period);
	}

	/**
	* @throws LogicException
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}
}