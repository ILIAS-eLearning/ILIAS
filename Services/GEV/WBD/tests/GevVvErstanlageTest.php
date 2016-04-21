<?php
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestVvErstanlage.php");
class GevVvErstanlageTest extends RequestTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		$data = array("address_type"=>"geschäftlich"
					  ,"gender"=>"m"
					  ,"email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"info_via_mail"=>false
					  ,"send_data"=>true
					  ,"data_secure"=>true
					  ,"birthday"=>"1981-06-19"
					  ,"country"=>"D"
					  ,"lastname"=>"Hecken"
					  ,"city"=>"Köln"
					  ,"zipcode"=>"50969"
					  ,"phone_nr"=>"0221/46757600"
					  ,"degree"=>"Dr"
					  ,"wbd_agent_status"=>"Makler"
					  ,"okz"=>"OKZ1"
					  ,"firstname"=>"Stefan"
					  ,"wbd_type"=>"3 - TP-Service"
					  ,"training_pass"=>true
					  ,"user_id"=>3215
					  ,"street"=>"Vorgebirgstr. 338"
					  ,"row_id"=>35214
					  ,"address_info"=>"Der wohnt bei Mutti"
					  ,"next_wbd_action"=>"1 - Erstanlage TP Service"
					);

		$this->request = gevWBDRequestVvErstanlage::getInstance($data);
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestVvErstanlage",$this->request);
	}

	public function xml_response_success() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<ErstanlageRueckgabewert>'
										.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
										.'<VermittlerId>20150728-100390-74</VermittlerId>'
										.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
										.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
									.'</ErstanlageRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
							.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
								.'<ErstanlageRueckgabewert>'
									.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
									.'<VermittlerId>20150728-100390-74</VermittlerId>'
									.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
									.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
								.'</ErstanlageRueckgabewert>'
							.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
							.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
								.'<ErstanlageRueckgabewert>'
									.'<TpInterneVermittlerId>7665</TpInterneVermittlerId>'
									.'<VermittlerId>20150728-100390-74</VermittlerId>'
									.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
									.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
								.'</ErstanlageRueckgabewert>'
							.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
			);
	}

	public function xml_response_success_xml_fails() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
							.'<soap:Body>'
							.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
								.'<ErstanlageRueckgabewert>'
									.'<TpIntersneVermittlerId>7665</TpIntersneVermittlerId>'
									.'<VermittlerdId>20150728-100390-74</VermittlerdId>'
									.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
									.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
								.'</ErstanlageRueckgabewert>'
							.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
							.'<soap:Body>'
							.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
								.'<ErstanlageRueckgabewert>'
									.'<TpInternesdVermittlerId>7665</TpInternesdVermittlerId>'
									.'<VermittlerId>20150728-100390-74</VermittlerId>'
									.'<AnlagedDastum>2015-07-28T00:00:00+02:00</AnlagedDastum>'
									.'<ZertifizierungsPeriode>2015-07-28T00:00:00+02:00</ZertifizierungsPeriode>'
								.'</ErstanlageRueckgabewert>'
							.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
							.'<soap:Body>'
							.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
								.'<ErstanlageRueckgabewert>'
									.'<TpInterasdneVermittlerId>7665</TpInterasdneVermittlerId>'
									.'<VermittlerId>20150728-100390-74</VermittlerId>'
									.'<AnlagedsaDatum>2015-07-28T00:00:00+02:00</AnlagedsaDatum>'
									.'<BeginnZertifizierungsPeriode>2015-07-28T00:00:00+02:00</BeginnZertifizierungsPeriode>'
								.'</ErstanlageRueckgabewert>'
							.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
			);
	}

	public function xml_response_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
							.'<soap:Body>'
								.'<soap:Fault>'
									.'<faultcode>soap:Server</faultcode>'
									.'<faultstring>Der Benutzer wurde von einem anderen TP angelegt: 5702136776</faultstring>'
									.'<detail>'
										.'<ns1:ExterneDoubletteException xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/" />'
									.'</detail>'
								.'</soap:Fault>'
							.'</soap:Body>'
						.'</soap:Envelope>'
			))
			);
	}

	//Bool = False needed
	/**
     * @dataProvider xml_response_success_xml_fails
     * @expectedException LogicException
     */
	public function test_parseResponseXMLErrorInXML($xml) {
		$this->request->createWBDSuccess($xml);
	}

	//Array needed
	/**
     * @dataProvider xml_response_success
     */
	public function test_returnWBDSuccessObject($xml) {
		$this->request->createWBDSuccess($xml);
		$this->assertInstanceOf("WBDSuccess",$this->request->getWBDSuccess());
	}

	/**
	 * @dataProvider xml_response_success
	 * @expectedException LogicException
	 */
	public function test_returnWBDErrorObjectOnSuccess($xml) {
		$this->request->createWBDSuccess($xml);
		$this->assertInstanceOf("WBDError",$this->request->getWBDError());
	}
}