<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Trait for gevWBDSuccessVvErtstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Service/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
class gevWBDSuccessVvErstanlage extends WBDSuccessVvErstanlage{
	use gevWBDSuccess;

	protected $row_id;

	public function __construct($response, $row_id) {
		$this->row_id = $row_id;
		parent::__construct($response);
	}
	/**
	* @throws LogicException
	* @return user_id
	*/
	public function rowId() {
		return $this->row_id;
	}
}