<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of GEV WBD Request for Service VvAenderung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVvAenderung.php");
require_once("Services/GEV/WBD/classes/Data/class.gevWBDData.php");
class gevWBDRequestVvAenderung extends WBDRequestVvAenderung {
	use gevWBDRequest;

	protected function __construct($data) {
		parent::__construct();

		$this->address_type 		= new gevWBDData("AdressTyp",$this->getDictionary()->getWBDName($data["address_type"],gevWBDDictionary::SERACH_IN_ADDRESS_TYPE));
		$this->address_info 		= new gevWBDData("AdressBemerkung",$data["address_info"]);
		$this->title 				= new gevWBDData("AnredeSchluessel",$this->getDictionary()->getWBDName($data["gender"],gevWBDDictionary::SERACH_IN_GENDER));
		$this->auth_email 			= new gevWBDData("AuthentifizierungsEmail",$data["email"]);
		$this->auth_mobile_phone_nr = new gevWBDData("AuthentifizierungsTelefonnummer",$data["mobile_phone_nr"]);
		$this->info_via_mail 		= new gevWBDData("BenachrichtigungPerEmail",$data["info_via_mail"]);

		$normal_email = ($data['wbd_email'] != '') ? $data['wbd_email'] : $data['email'];
		$this->email 				= new gevWBDData("Emailadresse",$normal_email);

		$this->birthday 			= new gevWBDData("Geburtsdatum",$data["birthday"]);
		$this->house_number			= new gevWBDData("Hausnummer",$data["house_number"]);
		$this->internal_agent_id 	= new gevWBDData("InterneVermittlerId",$data["user_id"]);
		$this->country 				= new gevWBDData("IsoLaendercode",$data["country"]);
		$this->lastname 			= new gevWBDData("Name",$data["lastname"]);
		$this->mobile_phone_nr 		= new gevWBDData("Mobilfunknummer",$data["mobile_phone_nr"]);
		$this->city 				= new gevWBDData("Ort",$data["city"]);
		$this->zipcode 				= new gevWBDData("Postleitzahl",$data["zipcode"]);
		$this->street 				= new gevWBDData("Strasse",$data["street"]);
		$this->phone_nr 			= new gevWBDData("Telefonnummer",$data["phone_nr"]);
		$this->degree 				= new gevWBDData("Titel",$data["degree"]);
		$this->agent_id 			= new gevWBDData("VermittlerId",$data["bwv_id"]);
		$this->wbd_agent_status 	= new gevWBDData("VermittlerStatus",$this->getDictionary()->getWBDName($data["wbd_agent_status"],gevWBDDictionary::SERACH_IN_AGENT_STATUS));
		$this->okz 					= new gevWBDData("VermittlungsTaetigkeit",$data["okz"]);
		$this->firstname 			= new gevWBDData("VorName",$data["firstname"]);

		$errors = $this->checkData($data);

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestVvAenderung::__construct:checkData failed",0,null, $errors);
		}

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);
		
		try {
			return new gevWBDRequestVvAenderung($data);
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
	private static function checkData(&$data) {
		$result = self::checkSzenarios($data);
		if(empty($result)) {
			if($data["phone_nr"] == "") {
				$data["phone_nr"] = $data["mobile_phone_nr"];
			}
		}
		return $result;
	}

	/**
	* creates the success object VvAenderung
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessVvAenderung($response,$this->row_id);
	}

	/**
	* gets the firstname
	*
	* @return string
	*/
	public function firstname() {
		return $this->firstname;
	}

	/**
	* gets the lasttname
	*
	* @return string
	*/
	public function lastname() {
		return $this->lastname;
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
	* @return string
	*/
	public function agentId() {
		return $this->agent_id;
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