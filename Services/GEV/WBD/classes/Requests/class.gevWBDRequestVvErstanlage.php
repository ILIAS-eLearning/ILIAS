<?php
require_once("/Library/WebServer/Documents/dev/4_4_generali2_new_wbd/Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("/Library/WebServer/Documents/dev/4_4_generali2_new_wbd/Services/GEV/WBD/classes/Requests/class.gevWBDRequest.php");
class gevWBDRequestVvErstanlage extends gevWBDRequest {
	
	static $request_type = "CREATE_USER";
	static $check_szenarios = array('title' 			=> array('mandatory'=>1,
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
									,'mobile_phone_nr' 	=> array('custom' => 'regexpMobilePhone')
									,'phone_nr'	 		=> array('custom' => 'regexpPhone')
									,'zipcode' 			=> array('mandatory'=>1, 'maxlen' => 10)
									,'city' 			=> array('mandatory'=>1, 'maxlen' => 50)
									,'street' 			=> array('mandatory'=>1, 'maxlen' => 50)
									,'house_number' 	=> array('mandatory'=>1, 'maxlen' => 10)
									,'email' 			=> array('mandatory' => 1)
									,'okz' 				=> array('mandatory'=>1, 
																 'list' => array(
																 	'OKZ1',
																 	'OKZ2',
																 	'OKZ3'
																 	)
																 )
									,'wbd_agent_status'	=> array('mandatory'=>1)
									,'wbd_type'			=> array('mandatory'=>1)
									,'send_data'		=> array('mandatory'=>1)
									,'data_secure'		=> array('mandatory'=>1)
									,'info_via_mail'	=> array('mandatory'=>1)
									,'training_pass'	=> array('mandatory'=>1)
									,'user_id'			=> array('mandatory'=>1)
									,'row_id'			=> array('mandatory'=>1)
								);

	public function __construct($data) {
		parent::__construct();

		$this->required_values = array("AdressTyp" => array("address_type",true, gevWBDDictionary::SERACH_IN_ADDRESS_TYPE)
								 ,"AdressBemerkung" => array("address_info",false)
								 ,"AnredeSchluessel" => array("title", true, gevWBDDictionary::SERACH_IN_GENDER)
								 ,"AuthentifizierungsEmail" => array("email",false)
								 ,"AuthentifizierungsTelefonnummer" => array("mobile_phone_nr",false)
								 ,"BenachrichtigungPerEmail" => array("info_via_mail",false)
								 ,"DatenuebermittlungsKennzeichen" => array("send_data",false)
								 ,"DatenschutzKennzeichen" => array("data_secure",false)
								 ,"Emailadresse" => array("email",false)
								 ,"Geburtsdatum" => array("birthday",false)
								 ,"Hausnummer" => array("house_number",false)
								 ,"InterneVermittlerId" => array("user_id",false)
								 ,"IsoLaendercode" => array("country",false)
								 ,"Name" => array("lastname",false)
								 ,"Mobilfunknummer" => array("mobile_phone_nr",false)
								 ,"Ort" => array("city",false)
								 ,"Postleitzahl" => array("zipcode",false)
								 ,"Strasse" => array("street",false)
								 ,"Telefonnummer" => array("phone_nr",false)
								 ,"Titel" => array("degree",false)
								 ,"VermittlerStatus" => array("wbd_agent_status",true, gevWBDDictionary::SERACH_IN_AGENT_STATUS)
								 ,"VermittlungsTaetigkeit" => array("okz",false)
								 ,"VorName" => array("firstname",false)
								 ,"TpKennzeichen" => array("wbd_type",true, gevWBDDictionary::SEARCH_IN_WBD_TYPE)
								 ,"WeiterbildungsAusweisBeantragt" => array("training_pass",false)
							);

		$this->response_success_values = array("TpInterneVermittlerId" => ""
										 ,"VermittlerId" => ""
										 ,"AnlageDatum" => ""
										 ,"BeginnZertifizierungsPeriode" => ""
									);
		
		$this->xml_tmpl_file = "VvErstanlage.xml";
		$this->wbd_service_name = "VvErstanlageService";

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];

		$this->fillRequestedValues($data);
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);

		if(self::checkData($data)) {
			return new gevWBDRequestVvErstanlage($data);
		}

		return null;
	}

	private static function checkData($values) {
		return self::checkSzenarios($values);
	}
}