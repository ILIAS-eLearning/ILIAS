<?php
class gevWBDRequestVvErstanlage extends WBDRequest {
	
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
									,'name_affix' 		=> array('maxlen' => 50)
									,'birthday' 		=> array('custom' => 'datebefore2000')
									,'email' 			=> array('mandatory' => 1)
									,'moblie_phone_nr' 	=> array('custom' => 'regexpMobilePhone')
									,'phone_nr'	 		=> array('custom' => 'regexpPhone')
									,'zipcode' 			=> array('mandatory'=>1, 'maxlen' => 10)
									,'city' 			=> array('mandatory'=>1, 'maxlen' => 50)
									,'street' 			=> array('mandatory'=>1, 'maxlen' => 50)
									,'house_number' 	=> array('mandatory'=>1, 'maxlen' => 10)
									,'pob' 				=> array('maxlen' => 30)
									,'free_text'		=> array('maxlen' => 50)
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
								);

	public function __construct($values) {
		parent::__construct();

		$required_values = array("AnredeSchluessel" => array("title",true)
								 ,"AuthentifizierungsEmail" => array("email",false)
								 ,"AuthentifizierungsTelefonnummer" => array("moblie_phone_nr",false)
								 ,"BenachrichtigungPerEmail" => array("info_via_mail",false)
								 ,"DatenuebermittlungsKennzeichen" => array("send_data",false)
								 ,"DatenschutzKennzeichen" => array("data_secure",false)
								 ,"Emailadresse" => array("email",false)
								 ,"Geburtsdatum" => array("birthday",false)
								 ,"IsoLaendercode" => array("country",false)
								 ,"Name" => array("lastname",false)
								 ,"Mobilfunknummer" => array("moblie_phone_nr",false)
								 ,"Ort" => array("city",false)
								 ,"Postleitzahl" => array("zipcode",false)
								 ,"Telefonnummer" => array("phone_nr",false)
								 ,"Titel" => array("degree",false)
								 ,"VermittlerStatus" => array("wbd_agent_status",true,)
								 ,"VermittlungsTaetigkeit" => array("okz",false)
								 ,"VorName" => array("firstname",false)
								 ,"TpKennzeichen" => array("wbd_type",true,)
								 ,"WeiterbildungsAusweisBeantragt" => array("training_pass",false)
							);

		$response_success_values = array("TpInterneVermittlerId" => ""
										 ,"VermittlerId" => ""
										 ,"AnlageDatum" => ""
										 ,"BeginnZertifizierungsPeriode" => ""
									);
		
		$xml_tmpl_file = "VvErstanlage.xml";
		$wbd_service_name = "VvErstanlageService";
	}

	public static function getInstance(array $data) {
		$data = self::polishInternalData($data);

		if(self::checkData($data)) {
			return new gevWBDRequestVvErstanlage($data);
		}

		return null;
	}

	private static function checkValues($values) {
		return self::checkSzenarios($values);
	}
}