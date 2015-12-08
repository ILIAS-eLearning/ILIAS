<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service WPMeldung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPStorno.php");
class gevWBDRequestWPStorno extends WBDRequestWPStorno {
	use gevWBDRequest;

	protected function __construct($data) {
		parent::__construct();

		$this->wbd_booking_id 	= new WBDData("WeiterbildungsPunkteBuchungsId",$data["wbd_booking_id"]);
		$this->bwv_id 			= new WBDData("VermittlerId",$data["bwv_id"]);
		
		$errors = $this->checkData($data);

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestWPStorno::__construct:checkData failed",0,null, $errors);
		}

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		try {
			return new gevWBDRequestWPStorno($data);
		}catch(myLogicException $e) {
			return $e->options();
		} catch(LogicException $e) {
			$errors = array();
			$errors[] =  self::createWBDError($e->getMessage(), static::$request_type, $data["user_id"], $data["row_id"],0);
			return $errors;
		}
	}

	/**
	* checked all given data
	*
	* @throws LogicException
	* 
	* @return string
	*/
	protected function checkData($data) {
		return $this->checkSzenarios($data);
	}

	/**
	* creates the success object VvErstanlage
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessWPStorno($response,$this->row_id);
	}

	/**
	* gets the wbd_booking_id
	*
	* @return string
	*/
	public function wbdBookingId() {
		return $this->wbd_booking_id;
	}

	/**
	* gets the row_id
	*
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}
}