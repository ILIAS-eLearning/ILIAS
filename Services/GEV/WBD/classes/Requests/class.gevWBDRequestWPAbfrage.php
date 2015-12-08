<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service WPAbfrage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPAbfrage.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
class gevWBDRequestWPAbfrage extends WBDRequestWPAbfrage {
	use gevWBDRequest;

	protected function __construct($data) {
		parent::__construct();

		$this->agent_id 				= new WBDData("VermittlerId",$data["bwv_id"]);
		$this->certification_period 	= new WBDData("ZertifizierungsPeriode",$this->dictionary->getWBDName($data["certification_period"],gevWBDDictionary::SEARCH_IN_CERTIFICATION_PERIOD));

		$errors = $this->checkData($data);

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestWPAbfrage::__construct:checkData failed",0,null, $errors);
		}

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		try {
			return new gevWBDRequestWPAbfrage($data);
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
		$this->wbd_success = new gevWBDSuccessWPAbfrage($response,$this->user_id);
	}

	/**
	* gets the agent_id
	*
	* @return string
	*/
	public function agentId() {
		return $this->agent_id;
	}
}