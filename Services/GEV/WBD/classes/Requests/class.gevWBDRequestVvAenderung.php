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
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVvAenderung.php");
require_once("Services/GEV/WBD/classes/Data/class.gevWBDData.php");
class gevWBDRequestVvAenderung extends gevWBDRequest {

	protected $address_type;
	protected $address_info;
	protected $title;
	protected $auth_email;
	protected $auth_mobile_phone_nr;
	protected $info_via_mail;
	protected $email;
	protected $birthday;
	protected $house_number;
	protected $internal_agent_id;
	protected $country;
	protected $lastname;
	protected $mobile_phone_nr;
	protected $city;
	protected $zipcode;
	protected $street;
	protected $phone_nr;
	protected $degree;
	protected $agent_id;
	protected $wbd_agent_status;
	protected $okz;
	protected $firstname;

	protected $xml_tmpl_file_name;

	static $request_type = "UPDATE_USER";
	static $check_szenarios = array('gender' 			=> array('mandatory'=>1,
															 	 'list'=> array(
															 	 		'm', 
															 	 		'f', 
															 	 		'w'
															 	 	)
															 	 )
									,'degree' 			=> array('maxlen' => 30)
									,'firstname' 		=> array('mandatory'=>1, 'maxlen' => 30)
									,'lastname' 		=> array('mandatory'=>1, 'maxlen' => 50)
									,'birthday' 		=> array('custom' => 'datebefore2000')
									,'email' 			=> array('mandatory' => 1)
									,'mobile_phone_nr' 	=> array('mandatory' => 1, 'custom' => 'regexpMobilePhone')
									,'phone_nr'	 		=> array('custom' => 'regexpPhone')
									,'zipcode' 			=> array('mandatory'=>1, 'maxlen' => 10)
									,'city' 			=> array('mandatory'=>1, 'maxlen' => 50)
									,'street' 			=> array('mandatory'=>1, 'maxlen' => 50)
									,'house_number' 	=> array('mandatory'=>1, 'maxlen' => 10)
									,'okz' 				=> array('mandatory'=>1, 
																 'list' => array(
																 	'OKZ1',
																 	'OKZ2',
																 	'OKZ3'
																 	)
																 )
									,'wbd_agent_status'	=> array('mandatory'=>1)
									,'info_via_mail'	=> array('mandatory'=>1,'custom'=>'isBool')
									,'user_id'			=> array('mandatory'=>1,'maxlen'=>50)
									,'row_id'			=> array('mandatory'=>1)
									,'address_type'		=> array('list' => array('','geschäftlich','privat','sonstiges'))
									,'address_info'		=> array('maxlen'=>50)
									,'country'			=> array('mandatory'=>1)
									,'bwv_id'			=> array('mandatory'=>1)
								);

	protected function __construct($data) {
		parent::__construct();

		$this->address_type 		= new gevWBDData("AdressTyp",$this->dictionary->getWBDName($data["address_type"],gevWBDDictionary::SERACH_IN_ADDRESS_TYPE));
		$this->address_info 		= new gevWBDData("AdressBemerkung",$data["address_info"]);
		$this->title 				= new gevWBDData("AnredeSchluessel",$this->dictionary->getWBDName($data["gender"],gevWBDDictionary::SERACH_IN_GENDER));
		$this->auth_email 			= new gevWBDData("AuthentifizierungsEmail",$data["email"]);
		$this->auth_mobile_phone_nr = new gevWBDData("AuthentifizierungsTelefonnummer",$data["mobile_phone_nr"]);
		$this->info_via_mail 		= new gevWBDData("BenachrichtigungPerEmail",$data["info_via_mail"]);
		$this->email 				= new gevWBDData("Emailadresse",$data["email"]);
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
		$this->wbd_agent_status 	= new gevWBDData("VermittlerStatus",$this->dictionary->getWBDName($data["wbd_agent_status"],gevWBDDictionary::SERACH_IN_AGENT_STATUS));
		$this->okz 					= new gevWBDData("VermittlungsTaetigkeit",$data["okz"]);
		$this->firstname 			= new gevWBDData("VorName",$data["firstname"]);

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];

		$this->xml_tmpl_file = "VvAenderung.xml";
		$this->wbd_service_name = "VvAnderungService";
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);
		$errors = self::checkData($data);
		if(!count($errors))  {
			return new gevWBDRequestVvAenderung($data);
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
	private static function checkData($values) {
		return self::checkSzenarios($values);
	}

	/**
	* creates the success object VvAenderung
	*
	* @throws LogicException
	* 
	* @return boolean
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessVvAenderung($response,$this->row_id);

		return true;
	}
}