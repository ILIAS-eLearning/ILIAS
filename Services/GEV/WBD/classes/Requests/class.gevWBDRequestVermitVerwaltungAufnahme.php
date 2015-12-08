<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service VermittlerVerwaltung
* part: Vermittler transferfähig machen
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVermitVerwaltungAufnahme.php");
class gevWBDRequestVermitVerwaltungAufnahme extends WBDRequestVermitVerwaltungAufnahme {
	use gevWBDRequest;

	protected function __construct($data) {
		parent::__construct();

		$this->auth_email 			= new WBDData("AuthentifizierungsEmail",$data["email"]);
		$this->auth_mobile_phone_nr = new WBDData("AuthentifizierungsTelefonnummer",$data["mobile_phone_nr"]);
		$this->agent_id 			= new WBDData("VermittlerId",$data["bwv_id"]);
		$this->firstname 			= new WBDData("VorName",$data["firstname"]);
		$this->lastname 			= new WBDData("Name",$data["lastname"]);
		$this->birthday 			= new WBDData("Geburtsdatum", $data["birthday"]);

		$errors = $this->checkData($data);

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestVermitVerwaltungAufnahme::__construct:checkData failed",0,null, $errors);
		}

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);

		try {
			return new gevWBDRequestVermitVerwaltungAufnahme($data);
		}catch(myLogicException $e) {
			return $e->options();
		} catch(LogicException $e) {
			$errors = array();
			$errors[] =  self::createWBDError($e->getMessage(), static::$request_type, $data["user_id"], $data["row_id"],0);
			return $errors;
		}
	}

	/**
	* creates the success object VermitVerwaltungTransferfaehig
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessVermitVerwaltungAufnahme($this->user_id,$this->row_id);
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
	* gets the row_id
	*
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets the user_id
	*
	* @return integer
	*/
	public function userId() {
		return $this->user_id;
	}

	/**
	* gets the agent_id
	*
	* @return integer
	*/
	public function agentId() {
		return $this->agent_id;
	}
}