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
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVermitVerwaltungTransferfaehig.php");
class gevWBDRequestVermitVerwaltungTransferfaehig extends gevWBDRequest {
	
	protected $auth_email;
	protected $auth_mobile_phone_nr;
	protected $agent_id;

	protected $xml_tmpl_file_name;

	static $request_type = "RELEASE_USER";
	static $check_szenarios = array('email' 			=> array('mandatory' => 1)
									,'mobile_phone_nr' 	=> array('mandatory' => 1, 'custom' => 'regexpMobilePhone')
									,'bwv_id'			=> array('mandatory'=>1)
								);

	protected function __construct($data) {
		parent::__construct();

		$this->auth_email 			= new gevWBDData("AuthentifizierungsEmail",$data["email"]);
		$this->auth_mobile_phone_nr = new gevWBDData("AuthentifizierungsTelefonnummer",$data["mobile_phone_nr"]);
		$this->agent_id 			= new gevWBDData("VermittlerId",$data["bwv_id"]);

		$this->xml_tmpl_file_name = "VermittlerVerwaltung_TransferfaehigMachen.xml";
		$this->wbd_service_name = "VermittlerVerwaltungService";

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);
		$errors = self::checkData($data);
		if(!count($errors))  {
			return new gevWBDRequestVermitVerwaltungTransferfaehig($data);
		} else {
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
	private static function checkData($data) {
		return self::checkSzenarios($data);
	}

	/**
	* creates the success object VermitVerwaltungTransferfaehig
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessVermitVerwaltungTransferfaehig($this->user_id,$this->row_id);
	}
}