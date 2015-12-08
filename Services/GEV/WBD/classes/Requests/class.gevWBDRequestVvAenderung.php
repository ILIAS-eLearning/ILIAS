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
class gevWBDRequestVvAenderung extends WBDRequestVvAenderung {
	use gevWBDRequest;

	protected function __construct($data) {
		parent::__construct();

		$this->address_type 		= new WBDData("AdressTyp",$this->getDictionary()->getWBDName($data["address_type"],gevWBDDictionary::SERACH_IN_ADDRESS_TYPE));
		$this->address_info 		= new WBDData("AdressBemerkung",$data["address_info"]);
		$this->title 				= new WBDData("AnredeSchluessel",$this->getDictionary()->getWBDName($data["gender"],gevWBDDictionary::SERACH_IN_GENDER));
		$this->auth_email 			= new WBDData("AuthentifizierungsEmail",$data["email"]);
		$this->auth_mobile_phone_nr = new WBDData("AuthentifizierungsTelefonnummer",$data["mobile_phone_nr"]);
		$this->info_via_mail 		= new WBDData("BenachrichtigungPerEmail",$data["info_via_mail"]);

		$normal_email = ($data['wbd_email'] != '') ? $data['wbd_email'] : $data['email'];
		$this->email 				= new WBDData("Emailadresse",$normal_email);
	
		$this->birthday 			= new WBDData("Geburtsdatum",$data["birthday"]);
		$this->house_number			= new WBDData("Hausnummer",$data["house_number"]);
		$this->internal_agent_id 	= new WBDData("InterneVermittlerId",$data["user_id"]);
		$this->country 				= new WBDData("IsoLaendercode",$data["country"]);
		$this->lastname 			= new WBDData("Name",$data["lastname"]);
		$this->mobile_phone_nr 		= new WBDData("Mobilfunknummer",$data["mobile_phone_nr"]);
		$this->city 				= new WBDData("Ort",$data["city"]);
		$this->zipcode 				= new WBDData("Postleitzahl",$data["zipcode"]);
		$this->street 				= new WBDData("Strasse",$data["street"]);
		$this->phone_nr 			= new WBDData("Telefonnummer",$data["phone_nr"]);
		$this->degree 				= new WBDData("Titel",$data["degree"]);
		$this->agent_id 			= new WBDData("VermittlerId",$data["bwv_id"]);
		$this->wbd_agent_status 	= new WBDData("VermittlerStatus",$this->getDictionary()->getWBDName($data["wbd_agent_status"],gevWBDDictionary::SERACH_IN_AGENT_STATUS));
		$this->okz 					= new WBDData("VermittlungsTaetigkeit",$data["okz"]);
		$this->firstname 			= new WBDData("VorName",$data["firstname"]);

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
	protected function checkData(&$data) {
		$result = $this->checkSzenarios($data);
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