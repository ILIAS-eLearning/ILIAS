<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service VvErstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVvErstanlage.php");
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
class gevWBDRequestVvErstanlage extends WBDRequestVvErstanlage {
	use gevWBDRequest;

	protected $error_group;

	protected function __construct($data) {
		$this->address_type 		= new WBDData("AdressTyp", $this->getDictionary()->getWBDName($data["address_type"], gevWBDDictionary::SERACH_IN_ADDRESS_TYPE));
		$this->address_info 		= new WBDData("AdressBemerkung", $data["address_info"]);
		$this->title 				= new WBDData("AnredeSchluessel", $this->getDictionary()->getWBDName($data["gender"], gevWBDDictionary::SERACH_IN_GENDER));
		$this->auth_email 			= new WBDData("AuthentifizierungsEmail", $data["email"]);
		$this->auth_mobile_phone_nr = new WBDData("AuthentifizierungsTelefonnummer", $data["mobile_phone_nr"]);
		$this->info_via_mail 		= new WBDData("BenachrichtigungPerEmail", $data["info_via_mail"]);
		$this->send_data 			= new WBDData("DatenuebermittlungsKennzeichen", $data["send_data"]);
		$this->data_secure 			= new WBDData("DatenschutzKennzeichen", $data["data_secure"]);
		
		$normal_email = ($data['wbd_email'] != '') ? $data['wbd_email'] : $data['email'];
		$this->email 				= new WBDData("Emailadresse", $normal_email);
		
		$this->birthday 			= new WBDData("Geburtsdatum", $data["birthday"]);
		$this->house_number			= new WBDData("Hausnummer", $data["house_number"]);
		$this->internal_agent_id 	= new WBDData("InterneVermittlerId", $data["user_id"]);
		$this->country 				= new WBDData("IsoLaendercode", $data["country"]);
		$this->lastname 			= new WBDData("Name", $data["lastname"]);
		$this->mobile_phone_nr 		= new WBDData("Mobilfunknummer", $data["mobile_phone_nr"]);
		$this->city 				= new WBDData("Ort", $data["city"]);
		$this->zipcode 				= new WBDData("Postleitzahl", $data["zipcode"]);
		$this->street 				= new WBDData("Strasse", $data["street"]);

		$this->phone_nr 			= new WBDData("Telefonnummer", ($data["phone_nr"] != "") ? $data["phone_nr"] : $data["mobile_phone_nr"]);
		
		$this->degree 				= new WBDData("Titel", $data["degree"]);
		$this->wbd_agent_status 	= new WBDData("VermittlerStatus", $this->getDictionary()->getWBDName($data["wbd_agent_status"], gevWBDDictionary::SERACH_IN_AGENT_STATUS));
		$this->okz 					= new WBDData("VermittlungsTaetigkeit",$data["okz"]);
		$this->firstname 			= new WBDData("VorName", $data["firstname"]);
		$this->wbd_type 			= new WBDData("TpKennzeichen", $this->getDictionary()->getWBDName($data["wbd_type"], gevWBDDictionary::SEARCH_IN_WBD_TYPE));

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
		$this->next_wbd_action = $data["next_wbd_action"];
		$this->error_group = gevWBDError::ERROR_GROUP_USER;
		
		$errors = $this->checkData();

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestVvErstanlage::__construct:checkData failed",0,null, $errors);
		}
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);
		
		try {
			return new gevWBDRequestVvErstanlage($data);
		}catch(myLogicException $e) {
			return $e->options();
		} catch(LogicException $e) {
			$errors = array();
			$errors[] =  self::createError($e->getMessage(), gevWBDError::ERROR_GROUP_USER, static::$request_type, $data["user_id"], $data["row_id"],0);
			return $errors;
		}
	}

	/**
	* checked all given data
	*
	* @return array
	*/
	protected function checkData() {
		$result = $this->checkSzenarios();
		return $result;
	}

	/**
	* creates the success object VvErstanlage
	*
	* @throws LogicException
	* 
	* @return boolean
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessVvErstanlage($response, (int)$this->row_id, $this->next_wbd_action);
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
	* gets the row_id
	*
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets a new WBD Error
	*
	* @return integer
	*/
	public function createWBDError($message) {
		$reason = $this->parseReason($message);
		$this->wbd_error = self::createError($reason, $this->error_group, $this->user_id, $this->row_id);
	}
}