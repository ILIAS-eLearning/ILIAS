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
require_once("Services/GEV/WBD/classes/Data/class.gevWBDData.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVermitVerwaltungAufnahme.php");
class gevWBDRequestVermitVerwaltungAufnahme extends gevWBDRequest {
	
	protected $auth_email;
	protected $auth_mobile_phone_nr;
	protected $agent_id;
	protected $firstname;
	protected $lastname;
	protected $birthday;

	protected $xml_tmpl_file_name;

	static $request_type = "AFFILIATE_USER";
	static $check_szenarios = array('email' 			=> array('mandatory' => 1)
									,'mobile_phone_nr' 	=> array('mandatory' => 1, 'custom' => 'regexpMobilePhone')
									,'bwv_id'			=> array('mandatory'=>1)
									,'firstname' 		=> array('mandatory' => 1, 'maxlen' => 30)
									,'lastname' 		=> array('mandatory' => 1, 'maxlen' => 50)
									,'birthday' 		=> array('mandatory' => 1,'custom' => 'datebefore2000')
								);

	protected function __construct($data) {
		parent::__construct();

		$this->auth_email 			= new gevWBDData("AuthentifizierungsEmail",$data["email"]);
		$this->auth_mobile_phone_nr = new gevWBDData("AuthentifizierungsTelefonnummer",$data["mobile_phone_nr"]);
		$this->agent_id 			= new gevWBDData("VermittlerId",$data["bwv_id"]);
		$this->firstname 			= new gevWBDData("VorName",$data["firstname"]);
		$this->lastname 			= new gevWBDData("Name",$data["lastname"]);
		$this->birthday 			= new gevWBDData("Geburtsdatum", $data["birthday"]);

		$this->xml_tmpl_file_name = "VermittlerVerwaltung_Aufnehmen.xml";
		$this->wbd_service_name = "VermittlerVerwaltungService";

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);
		$errors = self::checkData($data);

		if(!count($errors)) {
			try {
				return new gevWBDRequestVermitVerwaltungAufnahme($data);
			} catch(LogicException $e) {
				$errors = array();
				$errors[] =  new gevWBDError($e->getMessage(), static::$request_type, $data["user_id"], $data["row_id"]);
				return $errors;
			}
		} else {
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
	private static function checkData($data) {
		return self::checkSzenarios($data);
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