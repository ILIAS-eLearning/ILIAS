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
class gevWBDSuccessWPMeldung extends WBDSuccessWPMeldung {
	use gevWBDSuccess;
	
	protected $old_begin_of_certification;
	protected $user_id;

	public function __construct($response, $old_begin_of_certification, $user_id) {
		parent::__construct($response);

		$begin_of_certification_period = self::nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$this->begin_of_certification_period = $this->createDate($begin_of_certification_period);

		$this->old_begin_of_certification = $this->createDate($old_begin_of_certification);
		$this->user_id = $user_id;
	}

	/**
	* @throws LogicException
	* @return integer
	*/
	public function rowId() {
		return $this->internal_booking_id;
	}

	/**
	* gets the user_id
	* @return integer
	*/
	public function usrId() {
		return $this->user_id;
	}

	/**
	* should the begin_of_certification be updated
	*
	* @return boolean
	*/
	public function doUpdateBeginOfCertification(){
		if($this->begin_of_certification_period->get(IL_CAL_UNIX) != $this->old_begin_of_certification->get(IL_CAL_UNIX)) {
			return true;
		}

		return false;
	}
}